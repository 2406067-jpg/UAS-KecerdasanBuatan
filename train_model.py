import os
import pandas as pd
import joblib
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder, StandardScaler

# 1. Path Dataset dan Folder Model
csv_path = os.path.join('data', 'dataset', 'apple_products_dataset_100k.csv')
model_dir = 'model_saved'

if not os.path.exists(model_dir):
    os.makedirs(model_dir)

print("⏳ Membaca dataset yang sudah diperbarui...")
# low_memory=False biar error warning campuran tipe data lu tadi hilang masee
df = pd.read_csv(csv_path, low_memory=False)

print(f"📋 Total baris data saat ini: {len(df)}")
print(f"🔍 Nama kolom yang terdeteksi di CSV lu: {list(df.columns)}")

# =========================================================================
# 🛠️ OTOMATIS COCOKAN NAMA KOLOM LU (BIAR GAK KEYERROR LAGI)
# =========================================================================
kolom_model = None
kolom_garansi = None
kolom_bh = None
kolom_storage = None
kolom_target = None

for col in df.columns:
    col_lower = str(col).lower()
    if 'model' in col_lower or 'device' in col_lower or 'product' in col_lower:
        kolom_model = col
    elif 'garansi' in col_lower or 'warranty' in col_lower:
        kolom_garansi = col
    elif 'bh' in col_lower or 'battery' in col_lower or 'kesehatan' in col_lower:
        kolom_bh = col
    elif 'storage' in col_lower or 'memory' in col_lower or 'kapasitas' in col_lower:
        kolom_storage = col
    elif 'status' in col_lower or 'layak' in col_lower or 'label' in col_lower or 'rating' in col_lower:
        # Jika kolom keputusan namanya 'status' atau 'rating'
        kolom_target = col

# Jika kolom target masih kosong, pakai kolom terakhir sebagai keputusan/label target
if not kolom_target:
    kolom_target = df.columns[-1]

# Jika kolom penting tidak ketemu, kita ambil berdasarkan urutan atau default agar aman
kolom_model = kolom_model if kolom_model else df.columns[0]
kolom_garansi = kolom_garansi if kolom_garansi else (df.columns[1] if len(df.columns) > 1 else df.columns[0])
kolom_bh = kolom_bh if kolom_bh else (df.columns[2] if len(df.columns) > 2 else df.columns[0])
kolom_storage = kolom_storage if kolom_storage else (df.columns[3] if len(df.columns) > 3 else df.columns[0])

print(f"\n🎯 Menggunakan Kolom Fitur:")
print(f"   - Model   -> '{kolom_model}'")
print(f"   - Garansi -> '{kolom_garansi}'")
print(f"   - BH      -> '{kolom_bh}'")
print(f"   - Storage -> '{kolom_storage}'")
print(f"   - Target  -> '{kolom_target}'\n")

# Bersihkan baris yang kosong (Drop NaN) pada kolom yang dipakai agar tidak error saat training
df = df.dropna(subset=[kolom_model, kolom_garansi, kolom_bh, kolom_storage, kolom_target])

# =========================================================================
# ⚙️ 2. PREPROCESSING & ENCODING
# =========================================================================
print("⚙️ Melakukan encoding dan scaling data...")

le_model = LabelEncoder()
df['model_encoded'] = le_model.fit_transform(df[kolom_model].astype(str))

le_garansi = LabelEncoder()
df['garansi_encoded'] = le_garansi.fit_transform(df[kolom_garansi].astype(str))

# Satukan fitur angka dan teks yang sudah di-encode
X = pd.DataFrame({
    'model': df['model_encoded'],
    'garansi': df['garansi_encoded'],
    'bh': pd.to_numeric(df[kolom_bh], errors='coerce').fillna(80),
    'storage': pd.to_numeric(df[kolom_storage], errors='coerce').fillna(128)
})

y = df[kolom_target].astype(str)

# Scaling data angka
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Split data untuk validasi akurasi
X_train, X_test, y_train, y_test = train_test_split(X_scaled, y, test_size=0.2, random_state=42)

# =========================================================================
# 🔥 3. TRAINING RANDOM FOREST CLASSIFIER
# =========================================================================
print("🔥 Melatih kembali (Retraining) model Random Forest Classifier...")
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# Cek hasil akurasi model baru
akurasi = model.score(X_test, y_test)
print(f"✅ Training Selesai! Akurasi Model Baru: {akurasi * 100:.2f}%")

# =========================================================================
# 💾 4. SIMPAN DAN TIMPA FILE .PKL LAMA
# =========================================================================
print("💾 Menyimpan model terbaru ke folder model_saved/...")
joblib.dump(model, os.path.join(model_dir, 'iphone_classifier.pkl'))
joblib.dump(scaler, os.path.join(model_dir, 'scaler.pkl'))
joblib.dump(le_model, os.path.join(model_dir, 'encoder_model.pkl'))
joblib.dump(le_garansi, os.path.join(model_dir, 'encoder_garansi.pkl'))

print("\n🎉 MANTAP MASIEE! Semua file .pkl sukses diperbarui tanpa error!")