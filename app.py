from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
import os

app = Flask(__name__)

# Jalur menuju folder dataset di dalam data/dataset/
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATASET_DIR = os.path.join(BASE_DIR, 'data', 'dataset')

CSV_PATH_1 = os.path.join(DATASET_DIR, 'dataset_iphone_kelayakan.csv')
CSV_PATH_2 = os.path.join(DATASET_DIR, 'apple_products_dataset_100k.csv')

df_dataset = None

# =========================================================================
# 📊 PROSES SINKRONISASI RELASIONAL 2 FILE CSV (DENGAN AUTO-RENAME KOLOM)
# =========================================================================
try:
    if os.path.exists(CSV_PATH_1) and os.path.exists(CSV_PATH_2):
        df1 = pd.read_csv(CSV_PATH_1)
        df2 = pd.read_csv(CSV_PATH_2, low_memory=False)
        
        print(f"📊 CSV 1: {len(df1)} baris | CSV 2: {len(df2)} baris.")

        # 🔍 DETEKSI & SINKRONISASI NAMA KOLOM CSV 2 SECARA OTOMATIS
        if 'tipe_iphone' not in df2.columns:
            kolom_model_alternatif = [col for col in df2.columns if 'model' in col.lower() or 'tipe' in col.lower() or 'product' in col.lower()]
            if kolom_model_alternatif:
                df2 = df2.rename(columns={kolom_model_alternatif[0]: 'tipe_iphone'})
                print(f"🔄 Auto-Rename kolom '{kolom_model_alternatif[0]}' di CSV 2 menjadi 'tipe_iphone'")

        if 'kapasitas_storage' not in df2.columns:
            kolom_storage_alternatif = [col for col in df2.columns if 'storage' in col.lower() or 'rom' in col.lower() or 'kapasitas' in col.lower() or 'size' in col.lower()]
            if kolom_storage_alternatif:
                df2 = df2.rename(columns={kolom_storage_alternatif[0]: 'kapasitas_storage'})
                print(f"🔄 Auto-Rename kolom '{kolom_storage_alternatif[0]}' di CSV 2 menjadi 'kapasitas_storage'")

        # Pastikan tipe data kedua key sama (diubah ke string) agar merge tidak zonk
        df1['tipe_iphone'] = df1['tipe_iphone'].astype(str).str.strip()
        df1['kapasitas_storage'] = df1['kapasitas_storage'].astype(str).str.strip()
        df2['tipe_iphone'] = df2['tipe_iphone'].astype(str).str.strip()
        df2['kapasitas_storage'] = df2['kapasitas_storage'].astype(str).str.strip()

        # SINKRONISASI HORIZONTAL VIA MERGE (INNER JOIN)
        df_dataset = pd.merge(df1, df2, on=['tipe_iphone', 'kapasitas_storage'], how='inner')
        
        if df_dataset.empty:
            print("⚠️ Inner merge menghasilkan 0 baris. Beralih menggunakan metode Left Join...")
            df_dataset = pd.merge(df1, df2, on=['tipe_iphone', 'kapasitas_storage'], how='left')

        print(f"🔥 SINKRONISASI SUKSES! Total database terintegrasi: {len(df_dataset)} baris data.")
    else:
        if os.path.exists(CSV_PATH_1):
            df_dataset = pd.read_csv(CSV_PATH_1)
            print(f"⚠️ Hanya CSV 1 yang dimuat: {len(df_dataset)} baris.")
        elif os.path.exists(CSV_PATH_2):
            df_dataset = pd.read_csv(CSV_PATH_2, low_memory=False)
            print(f"⚠️ Hanya CSV 2 yang dimuat: {len(df_dataset)} baris.")
        else:
            print("❌ ERROR CRITICAL: Kedua file CSV tidak ditemukan di folder tujuan.")
except Exception as e:
    df_dataset = None
    print(f"❌ Gagal memproses sinkronisasi data CSV: {str(e)}")


def safe_int(value, default=0):
    try:
        if value is None or str(value).strip() == "":
            return default
        return int(float(str(value).strip()))
    except (ValueError, TypeError):
        return default

@app.route('/predict', methods=['POST'])
def predict():
    if df_dataset is None or df_dataset.empty:
        return jsonify({
            'status': 'error',
            'reply': '📊 <b>Engine Error:</b> Gabungan database CSV kelayakan tidak ditemukan atau kosong.'
        }), 500

    try:
        data = request.json or {}
        
        # 1. Tangkap parameter asli kiriman user
        tipe_iphone_id = safe_int(data.get('tipe_iphone'), 0)
        storage_input = safe_int(data.get('kapasitas_storage'), 0)
        bh_input = safe_int(data.get('battery_health'), 0)
        garansi_status = safe_int(data.get('garansi_status'), 0)
        sinyal_status = safe_int(data.get('sinyal_status'), 0)
        biometrik_status = safe_int(data.get('biometrik_status'), 0)
        part_status = safe_int(data.get('part_status'), 0)
        kamera_status = safe_int(data.get('kamera_status'), 0)
        icloud_status = safe_int(data.get('icloud_status'), 0)
        jangka_pakai = safe_int(data.get('jangka_pakai'), 1)
        skor_persen = safe_int(data.get('skor_persen'), 100)

        # 4. MAPPING DATA UNTUK TRANSLATE ID KE TEXT ASLI DATASET
        tipe_map = {
            0: "iPhone X", 1: "iPhone XR", 2: "iPhone XS", 3: "iPhone XS Max",
            4: "iPhone 11", 5: "iPhone 11 Pro", 6: "iPhone 11 Pro Max",
            7: "iPhone 12 Mini", 8: "iPhone 12", 9: "iPhone 12 Pro", 10: "iPhone 12 Pro Max",
            11: "iPhone 13 Mini", 12: "iPhone 13", 13: "iPhone 13 Pro", 14: "iPhone 13 Pro Max",
            15: "iPhone 14", 16: "iPhone 14 Plus", 17: "iPhone 14 Pro", 18: "iPhone 14 Pro Max",
            19: "iPhone 15", 20: "iPhone 15 Plus", 21: "iPhone 15 Pro", 22: "iPhone 15 Pro Max",
            23: "iPhone 16", 24: "iPhone 16 Plus", 25: "iPhone 16 Pro", 26: "iPhone 16 Pro Max",
            27: "iPhone 17", 28: "iPhone 17 Plus", 29: "iPhone 17 Pro", 30: "iPhone 17 Pro Max"
        }

        # Dapatkan nama string target berdasarkan ID (Contoh: ID 9 -> "iPhone 12 Pro")
        tipe_string_target = tipe_map.get(tipe_iphone_id, "")

        # 2. STRATEGI FILTER PRIORITAS UTAMA (Pencocokan String Nama Perangkat & Storage)
        df_filtered = df_dataset.copy()
        
        # Filter berdasarkan string nama iPhone jika kolom dan target string valid
        if 'tipe_iphone' in df_filtered.columns and tipe_string_target != "":
            df_filtered = df_filtered[df_filtered['tipe_iphone'].astype(str).str.lower().str.strip() == tipe_string_target.lower().strip()]
        
        # Filter berdasarkan kapasitas storage
        if 'kapasitas_storage' in df_filtered.columns:
            df_filtered = df_filtered[df_filtered['kapasitas_storage'].astype(str).str.strip() == str(storage_input)]
            
        # Jika kombinasi nama + storage kosong, lakukan fallback ke model yang sama tanpa batasan storage
        if df_filtered.empty and 'tipe_iphone' in df_dataset.columns and tipe_string_target != "":
            df_filtered = df_dataset[df_dataset['tipe_iphone'].astype(str).str.lower().str.strip() == tipe_string_target.lower().strip()].copy()

        # Jika hasil filter masih kosong total, gunakan seluruh dataset agar sistem tidak crash
        if df_filtered.empty:
            df_filtered = df_dataset.copy()

        # List fitur untuk dihitung kesamaan jaraknya
        fitur_kolom = [
            'battery_health', 'garansi_status', 'sinyal_status', 
            'biometrik_status', 'part_status', 'kamera_status', 
            'icloud_status', 'jangka_pakai', 'skor_persen'
        ]
        
        kolom_valid = [col for col in fitur_kolom if col in df_filtered.columns]

        # Vektor inputan user disesuaikan dengan kolom yang valid
        input_vector = np.array([
            bh_input, garansi_status, sinyal_status,
            biometrik_status, part_status, kamera_status,
            icloud_status, jangka_pakai, skor_persen
        ])[:len(kolom_valid)]

        # Beri BOBOT BERAT (Weight) pada Battery Health dan Skor Persen
        weights = np.ones(len(kolom_valid))
        if 'battery_health' in kolom_valid:
            weights[kolom_valid.index('battery_health')] = 5.0  
        if 'skor_persen' in kolom_valid:
            weights[kolom_valid.index('skor_persen')] = 3.0

        # Isi data kosong (NaN) di dataset dengan nilai 0 agar hitungan Euclidean aman
        matrix_csv = df_filtered[kolom_valid].fillna(0).values
        diff = matrix_csv - input_vector
        distances = np.linalg.norm(diff * weights, axis=1)

        # Temukan baris paling mirip di dalam gabungan CSV
        indeks_terdekat = np.argmin(distances)
        baris_tercocok = df_filtered.iloc[indeks_terdekat]
        
        # 3. AMBIL DATA TARGET KELAYAKAN & MAPPING KODE ANGKA KE TEXT
        target_col = 'label_kelayakan' if 'label_kelayakan' in df_filtered.columns else ('label' if 'label' in df_filtered.columns else None)
        
        if target_col and pd.notna(baris_tercocok[target_col]):
            nilai_mentah = str(baris_tercocok[target_col]).strip()
            if nilai_mentah in ['0', '0.0']:
                status_akhir = "TIDAK LAYAK / SEBAIKNYA DIHINDARI ❌"
            elif nilai_mentah in ['1', '1.0']:
                status_akhir = "LAYAK BELI (KONDISI SEDANG) ⚠️"
            elif nilai_mentah in ['2', '2.0']:
                status_akhir = "SANGAT LAYAK BELI (LIKE NEW) ✨"
            else:
                status_akhir = nilai_mentah.upper()
        else:
            status_akhir = "LAYAK BELI (KONDISI SEDANG) ⚠️"

        # Tentukan penamaan dinamis untuk output log/display user
        tipe_input = tipe_string_target if tipe_string_target != "" else f"iPhone (Model ID: {tipe_iphone_id})"
        
        garansi_map = {0: "iBox / Resmi", 1: "Internasional (Inter)", 2: "WiFi Only"}
        garansi_input = garansi_map.get(garansi_status, "WiFi Only")

        sinyal_txt = "❌ Terblokir (WiFi Only)" if sinyal_status == 2 else "✅ Aman All Operator"
        icloud_txt = "❌ Terkunci / Nyangkut" if icloud_status == 1 else "✅ Bersih (Bebas Reset)"
        bio_txt = "❌ Rusak (Face ID/Touch ID Off)" if biometrik_status == 1 else "✅ Normal & Aktif"
        part_txt = "❌ True Tone Off / LCD Gantian" if part_status == 1 else "✅ Komponen Original (True Tone On)"
        kam_txt = "❌ Minus (Kamera Getar/Blur)" if kamera_status == 1 else "✅ Jernih / OIS Normal"

        reply_message = (
            f"📋 <b>Hasil Analisis Kelayakan Perangkat:</b><br>"
            f"• Tipe Perangkat: <b>{tipe_input} {storage_input}GB</b><br>"
            f"• Kesehatan Baterai (BH): <b>{bh_input}%</b><br>"
            f"• Distribusi / Garansi: <b>{garansi_input}</b><br>"
            f"• Jangka Pakai: <b>{jangka_pakai} Tahun</b><br>"
            f"• Skor Kelayakan Fisik: <b>{skor_persen}%</b><br><br>"
            f"🛠️ <b>Status Diagnosa Hardware:</b><br>"
            f"• Jaringan Sinyal: {sinyal_txt}<br>"
            f"• Akun iCloud: {icloud_txt}<br>"
            f"• Sensor Biometrik: {bio_txt}<br>"
            f"• Keaslian Part: {part_txt}<br>"
            f"• Kamera Utama: {kam_txt}<br><br>"
            f"⚖️ <b>Rekomendasi Keputusan:</b><br>"
            f"Berdasarkan dataset terintegrasi, unit ini dinyatakan: <b>{status_akhir}</b>"
        )
        
        return jsonify({
            'status': 'success',
            'reply': reply_message,
            'extracted_data': {
                'model_device': f"{tipe_input} {storage_input}GB",
                'battery_health': bh_input,
                'storage_size': storage_input
            }
        })

    except Exception as e:
        return jsonify({
            'status': 'error',
            'reply': f'⚠️ <b>System Error CSV Precision Matching:</b> {str(e)}'
        }), 400

if __name__ == '__main__':
    print("🚀 Server Python AI Aktif! Mode Sinkronisasi Relasional Komprehensif Aktif...")
    app.run(host='127.0.0.1', port=5000, debug=False)