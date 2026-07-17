import mysql.connector
import pandas as pd
import os
import re

# =========================================================================
# ⚙️ 1. KONFIGURASI KONEKSI DATABASE & FILE PATH
# =========================================================================
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'nevorix_ios'
}

csv_path = os.path.join('data', 'dataset', 'apple_products_dataset_100k.csv')

# =========================================================================
# 🛠️ 2. FUNGSI EKSTRAKSI GABUNGAN (SUPER PINTER)
# =========================================================================
def extract_from_session(combined_text):
    """
    Mengekstrak spesifikasi dari gabungan seluruh chat dalam satu sesi/user
    agar tidak ada data yang terlewat atau terisi nilai default yang salah.
    """
    model = "iPhone 13"
    garansi = "Inter"
    bh = 90
    storage = 128

    text_lower = combined_text.lower()

    # 1. Deteksi Model
    if re.search(r'iphone\s?(\d+|x|xs|xr|pro|max)', text_lower):
        match = re.search(r'iphone\s?(\d+|x|xs|xr|pro|max)', text_lower)
        model = f"iPhone {match.group(1).upper()}"
    elif re.search(r'\b(ip)\s?(\d+|x|xs|xr|pro|max)', text_lower):
        match = re.search(r'\b(ip)\s?(\d+|x|xs|xr|pro|max)', text_lower)
        model = f"iPhone {match.group(2).upper()}"

    # 2. Deteksi Garansi & Kondisi Pasar Gelap Indonesia
    if 'ibox' in text_lower or 'kemenperin' in text_lower:
        garansi = "iBox"
    elif 'bypass' in text_lower:
        garansi = "Bypass"
    elif 'wifi only' in text_lower or 'smartfren only' in text_lower:
        garansi = "WiFi Only"
    elif 'bea cukai' in text_lower or 'beacukai' in text_lower or 'pajak' in text_lower:
        garansi = "Inter (Tax Paid)"
    elif 'inter' in text_lower:
        garansi = "Inter"

    # 3. Deteksi Battery Health (BH) - Cari angka di sekitar kata 'bh' atau 'baterai'
    bh_match = re.search(r'(?:bh|baterai|bateri|health)\s*([6-9]\d|100)', text_lower)
    if bh_match:
        bh = int(bh_match.group(1))
    else:
        # Jika user cuma ngetik angka doang di akhir sesi chat (misal: "74")
        all_numbers = re.findall(r'\b([6-9]\d|100)\b', text_lower)
        if all_numbers:
            bh = int(all_numbers[-1]) # Ambil angka persentase terakhir

    # 4. Deteksi Storage (Kapasitas Memori)
    storage_match = re.search(r'\b(64|128|256|512|1024)\b', text_lower)
    if storage_match:
        storage = int(storage_match.group(1))

    return model, garansi, bh, storage

# =========================================================================
# 🚀 3. PROSES PENGGABUNGAN DATA & SINKRONISASI
# =========================================================================
try:
    print("🔄 Menghubungkan ke database MySQL nevorix_ios...")
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    # Ambil chat history. Jika ada kolom 'session_id' atau 'user_id', 
    # lu bisa ganti agar penggabungan teksnya jauh lebih sempurna per user.
    query = "SELECT user_message FROM chat_history"
    cursor.execute(query)
    rows = cursor.fetchall()
    
    if not rows:
        print("❌ Selesai: Tidak ada data di tabel 'chat_history' masee.")
    else:
        # Satukan semua baris chat menjadi satu teks raksasa untuk dianalisis polanya
        all_chats_combined = " ".join([str(r['user_message']) for r in rows])
        
        if not os.path.exists(csv_path):
            print(f"❌ Error: File dataset tidak ditemukan di '{csv_path}'!")
        else:
            # Ambil layout kolom asli dataset
            df_existing = pd.read_csv(csv_path, nrows=1)
            columns_layout = df_existing.columns.tolist()
            
            # Ekstrak data yang sesungguhnya dari gabungan riwayat chat
            model, garansi, bh, storage = extract_from_session(all_chats_combined)
            
            # Petakan ke dalam baris terstruktur baru
            data_mapped = {}
            for col in columns_layout:
                col_lower = col.lower()
                if 'model' in col_lower or 'device' in col_lower or 'name' in col_lower:
                    data_mapped[col] = model
                elif 'garansi' in col_lower or 'warranty' in col_lower:
                    data_mapped[col] = garansi
                elif 'bh' in col_lower or 'battery' in col_lower or 'mah' in col_lower:
                    data_mapped[col] = bh
                elif 'storage' in col_lower or 'memory' in col_lower:
                    data_mapped[col] = storage
                else:
                    data_mapped[col] = "Second"
            
            # Masukkan sebagai 1 data riwayat utuh yang VALID ke CSV
            df_new_entry = pd.DataFrame([data_mapped])
            df_new_entry = df_new_entry[columns_layout]
            
            df_new_entry.to_csv(csv_path, mode='a', header=False, index=False)
            
            print("\n=========================================================")
            print(f"🔥 SINKRONISASI BERHASIL! AI Berhasil Belajar Data Nyata:")
            print(f"   👉 Model   : {model}")
            print(f"   👉 Garansi : {garansi}")
            print(f"   👉 BH      : {bh}%")
            print(f"   👉 Storage : {storage} GB")
            print(f"   Sukses digabungkan ke paling bawah '{csv_path}'!")
            print("=========================================================")

except mysql.connector.Error as err:
    print(f"❌ Error database: {err}")
finally:
    if 'conn' in locals() and conn.is_connected():
        cursor.close()
        conn.close()