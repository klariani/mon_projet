import os
import json
import datetime
import pandas as pd
import numpy as np

from flask import Flask, request, jsonify
from flask_cors import CORS
from werkzeug.utils import secure_filename

import tensorflow as tf
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing import image

# ============================================================
# Configuration & Paramètres
# ============================================================
BASE_DIR = "C:/wamp64/www/GestionP"
MODEL_PATH = os.path.join(BASE_DIR, "model_cnn_classification.keras")
UPLOAD_FOLDER = os.path.join(BASE_DIR, "static", "uploads")
HISTORY_FILE = os.path.join(BASE_DIR, "predictions_history.csv")
TEST_RESULTS_PATH = os.path.join(BASE_DIR, "Test_template", "test_resultat.json")
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg'}
TARGET_SIZE = (224, 224)
DATASET_PATH = os.path.join(BASE_DIR, "dataset_Resized")

# ============================================================
# Vérifications initiales
# ============================================================
if not os.path.exists(DATASET_PATH):
    print(f"❌ ERREUR : Le dossier '{DATASET_PATH}' est introuvable.")
    exit(1)

if not os.path.exists(MODEL_PATH):
    print(f"❌ ERREUR : Le modèle '{MODEL_PATH}' est introuvable.")
    exit(1)

# ============================================================
# Récupération des classes
# ============================================================
classes = sorted(os.listdir(DATASET_PATH))
if not classes:
    print("❌ ERREUR : Aucune classe trouvée dans 'dataset_Resized'.")
    exit(1)

print(f"✅ Classes détectées : {classes}")

# ============================================================
# Chargement et configuration du modèle
# ============================================================
print("⏳ Chargement du modèle...")
model = load_model(MODEL_PATH)
model.compile(optimizer='adam', loss='categorical_crossentropy', metrics=['accuracy'])
print("✅ Modèle chargé et compilé avec succès !")

# ============================================================
# Création des dossiers/fichiers requis
# ============================================================
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
if not os.path.exists(HISTORY_FILE):
    pd.DataFrame(columns=['date', 'image', 'classe', 'confiance']).to_csv(HISTORY_FILE, index=False)

# ============================================================
# Initialisation de Flask
# ============================================================
app = Flask(__name__, static_folder='static', template_folder='.')
CORS(app, resources={r"/*": {"origins": "*"}})

# ============================================================
# Fonctions Utilitaires
# ============================================================
def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def save_prediction_history(image_name, classe, confiance):
    new_entry = pd.DataFrame([{
        'date': datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        'image': image_name,
        'classe': classe,
        'confiance': confiance
    }])
    new_entry.to_csv(HISTORY_FILE, mode='a', header=False, index=False)

def timestamped_filename(original_filename):
    timestamp = datetime.datetime.now().strftime("%Y%m%d%H%M%S")
    basename, ext = os.path.splitext(secure_filename(original_filename))
    return f"{basename}_{timestamp}{ext}"

# ============================================================
# Endpoints de l'API Flask
# ============================================================
@app.route('/')
def home():
    return "API Flask - Prédictions"

@app.route('/predict', methods=['POST'])
def predict():
    try:
        if 'file' not in request.files:
            return jsonify({'error': 'Aucun fichier fourni'}), 400
        file = request.files['file']
        if file.filename == '':
            return jsonify({'error': 'Fichier vide'}), 400
        if not allowed_file(file.filename):
            return jsonify({'error': 'Format non supporté'}), 400

        filename = timestamped_filename(file.filename)
        save_path = os.path.join(UPLOAD_FOLDER, filename)
        file.save(save_path)

        img = image.load_img(save_path, target_size=TARGET_SIZE)
        img_array = image.img_to_array(img) / 255.0
        img_array = np.expand_dims(img_array, axis=0)

        preds = model.predict(img_array)
        class_idx = int(np.argmax(preds[0]))
        confiance = float(np.max(preds[0]) * 100)
        classe_predite = classes[class_idx]

        save_prediction_history(filename, classe_predite, confiance)

        image_url = f'http://localhost:5000/static/uploads/{filename}'

        return jsonify({
            'classe': classe_predite,
            'confiance': confiance,
            'image_path': image_url
        })

    except Exception as e:
        print(f'❌ ERREUR : {str(e)}')
        return jsonify({'error': 'Erreur interne', 'details': str(e)}), 500

@app.route('/model_info', methods=['GET'])
def model_info():
    try:
        with open(TEST_RESULTS_PATH, 'r') as f:
            data = json.load(f)
        return jsonify(data)
    except Exception as e:
        print(f"❌ Erreur : {e}")
        return jsonify({'error': f'Impossible de lire le fichier JSON : {str(e)}'}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
