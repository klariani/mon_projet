from flask import Flask, request, jsonify, render_template, redirect
from flask_cors import CORS
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing import image
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from werkzeug.utils import secure_filename
import pandas as pd
import numpy as np
import os
import json
import datetime

app = Flask(__name__, static_folder='static', template_folder='.')
CORS(app, resources={r"/*": {"origins": "*"}})

# 📌 Configuration
BASE_DIR = "C:/wamp64/www/GestionP"
DATASET_PATH = os.path.join(BASE_DIR, "dataset_Resized")
MODEL_PATH = os.path.join(BASE_DIR, "model_cnn_classification.h5")
UPLOAD_FOLDER = os.path.join(BASE_DIR, "static/uploads")
HISTORY_FILE = os.path.join(BASE_DIR, "predictions_history.csv")
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg'}
TARGET_SIZE = (224, 224)

# 📌 Vérifier que le dataset existe
if not os.path.exists(DATASET_PATH):
    print(f"❌ ERREUR : Le dossier '{DATASET_PATH}' est introuvable.")
    exit(1)

# 📌 Charger dynamiquement les noms des classes depuis le dataset
classes = sorted(os.listdir(DATASET_PATH))
if len(classes) == 0:
    print("❌ ERREUR : Aucune classe trouvée dans 'dataset_Resized'.")
    exit(1)
print(f"✅ Classes détectées : {classes}")

# 📌 Vérifier si le modèle existe avant de le charger
if not os.path.exists(MODEL_PATH):
    print(f"❌ ERREUR : Le fichier du modèle '{MODEL_PATH}' est introuvable.")
    exit(1)

# 📌 Charger le modèle
print("⏳ Chargement du modèle...")
model = load_model(MODEL_PATH)
model.compile(optimizer='adam', loss='categorical_crossentropy', metrics=['accuracy'])
print("✅ Modèle chargé et compilé avec succès !")

# 📌 Créer le dossier pour stocker les images envoyées s'il n'existe pas
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

# 📌 Créer le fichier d'historique s'il n'existe pas
if not os.path.exists(HISTORY_FILE):
    pd.DataFrame(columns=['date', 'image', 'classe', 'confiance']).to_csv(HISTORY_FILE, index=False)

def allowed_file(filename):
    """ Vérifie si l'extension du fichier est valide """
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def save_prediction_history(image_name, classe, confiance):
    """ Enregistrer l'historique des prédictions """
    new_entry = pd.DataFrame([{
        'date': datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        'image': image_name,
        'classe': classe,
        'confiance': confiance
    }])
    new_entry.to_csv(HISTORY_FILE, mode='a', header=False, index=False)

@app.route('/')
def home():
    """ Page d'accueil pour le frontend """
    return render_template('prediction.html')

@app.route('/dashboard')
def dashboard():
    """ Redirige vers le dashboard interactif """
    return redirect("http://localhost:8050")

@app.route('/predict', methods=['POST'])
def predict():
    """ Gère la prédiction d'une image envoyée par l'utilisateur """
    try:
        print("📥 Requête reçue sur /predict")
        
        if 'file' not in request.files:
            print("⚠️ Aucun fichier reçu")
            return jsonify({'error': 'Aucun fichier fourni'}), 400

        file = request.files['file']
        if file.filename == '':
            print("⚠️ Fichier vide")
            return jsonify({'error': 'Fichier vide'}), 400

        if not allowed_file(file.filename):
            print("⚠️ Format non supporté")
            return jsonify({'error': 'Format non supporté'}), 400

        # 📌 Sauvegarde du fichier
        filename = secure_filename(file.filename)
        save_path = os.path.join(UPLOAD_FOLDER, filename)
        file.save(save_path)
        print(f"✅ Fichier enregistré à : {save_path}")

        # 📌 Charger et prétraiter l'image
        img = image.load_img(save_path, target_size=TARGET_SIZE)
        img_array = image.img_to_array(img) / 255.0
        img_array = np.expand_dims(img_array, axis=0)

        # 📌 Prédiction avec le modèle
        preds = model.predict(img_array)
        class_idx = int(np.argmax(preds[0]))
        confiance = float(np.max(preds[0]) * 100)
        classe_predite = classes[class_idx]

        print(f"🎯 Classe prédite : {classe_predite} avec {confiance:.2f}% de confiance")

        # 📌 Sauvegarder l'historique des prédictions
        save_prediction_history(filename, classe_predite, confiance)

        return jsonify({
            'classe': classe_predite,
            'confiance': confiance,
            'image_path': f'http://localhost:5000/static/uploads/{filename}'
        })

    except Exception as e:
        print(f'❌ ERREUR: {str(e)}')
        return jsonify({'error': 'Erreur de traitement'}), 500

@app.route('/training_status', methods=['GET'])
def training_status():
    """ Vérifier le statut d'entraînement du modèle """
    try:
        training_log_path = os.path.join(BASE_DIR, 'training_log.json')
        with open(training_log_path, 'r') as f:
            log_data = json.load(f)
        return jsonify(log_data)
    except Exception as e:
        print(f"❌ Erreur de lecture du log d'entraînement : {str(e)}")
        return jsonify({'error': 'Impossible de lire le fichier log'}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
