#Bibliothèque nécessaires
import pandas as pd
import numpy as np
import plotly.express as px
import plotly.graph_objects as go
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler
import os
from sqlalchemy import create_engine
import pymysql

#  Définir le chemin de sauvegarde des figures HTML 
img_path = "C:/mamp/htdocs/mon_projet/img"  # Dossier où les images seront sauvegardées
os.makedirs(img_path, exist_ok=True)  # Créer le dossier s'il n'existe pas

# Connexion à la base de données
user = "root"
password = "root"
host = "localhost"
database = "cancer"

engine = create_engine(f"mysql+pymysql://{user}:{password}@{host}/{database}")
query = "SELECT * FROM tumeur"
df = pd.read_sql(query, engine)
engine.dispose()

# Vérification si les données sont vides
if df.empty:
    print("La table 'tumeur' est vide.")
    exit()

# Remplacement des valeurs de 'code_diagnostic' par des étiquettes 'B' et 'M'
df['Tumeur'] = df['code_diagnostic'].map({1: 'B', 2: 'M'})

# Sélection des caractéristiques et de la cible
X = df.iloc[:, 1:-1]  # Variables indépendantes
y = df['Tumeur']  # Variable cible (type de tumeur)

# Normalisation des données
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Réduction de dimension avec PCA
pca = PCA(n_components=2)  # Réduction à 2 dimensions
X_pca = pca.fit_transform(X_scaled)

# Création d'un DataFrame pour les résultats PCA
df_pca = pd.DataFrame(X_pca, columns=['CP1', 'CP2'])  # Composantes principales
df_pca['Tumeur'] = y  # Ajouter la variable cible pour la coloration

# Création du graphique interactif PCA avec cercles autour des groupes
fig_pca = px.scatter(df_pca, x='CP1', y='CP2', color='Tumeur', 
                     labels={'CP1': 'Composante Principale 1', 'CP2': 'Composante Principale 2'},
                     title="Analyse en Composantes Principales des Tumeurs")

# Ajouter des cercles autour des points des groupes 'B' et 'M'
for label in df_pca['Tumeur'].unique():
    group_data = df_pca[df_pca['Tumeur'] == label]
    mean_x = group_data['CP1'].mean()
    mean_y = group_data['CP2'].mean()
    
    std_x = group_data['CP1'].std()
    std_y = group_data['CP2'].std()
    radius = max(std_x, std_y) * 1.5  # Calculer le rayon du cercle
    
    fig_pca.add_shape(
        type="circle",
        xref="x", yref="y",
        x0=mean_x - radius, y0=mean_y - radius,
        x1=mean_x + radius, y1=mean_y + radius,
        line=dict(color='black', width=2, dash='dot'),
        name=f"Cercle {label}"
    )

# Personnalisation du graphique
fig_pca.update_layout(legend_title="Type de Tumeur")
fig_pca.update_traces(marker=dict(size=8, opacity=0.6), selector=dict(mode='markers'))

# Sauvegarder le graphique interactif
fig_pca.write_html(os.path.join(img_path, 'pca_interactif_avec_cercles.html'))  # Sauvegarde du graphique

# Création d'une figure vide
fig = go.Figure()

# Ajouter les boxplots pour chaque caractéristique
fig.add_trace(go.Box(
    x=df['Tumeur'],
    y=df['air_moyenne'],
    name='Air Moyenne',
    boxmean='sd',
    marker=dict(color='blue')
))

fig.add_trace(go.Box(
    x=df['Tumeur'],
    y=df['aire_se'],
    name='Aire Standardisée',
    boxmean='sd',
    marker=dict(color='green')
))

fig.add_trace(go.Box(
    x=df['Tumeur'],
    y=df['aire_worst'],
    name='Aire Worst',
    boxmean='sd',
    marker=dict(color='red')
))

# Personnalisation du graphique
fig.update_layout(
    title="Boxplots des Caractéristiques des Tumeurs",
    xaxis_title="Type de Tumeur",
    yaxis_title="Valeur des Caractéristiques",
    boxmode='group',
    legend_title="Caractéristique",
    showlegend=True
)

# Sauvegarder le graphique combiné
fig.write_html(os.path.join(img_path, 'boxplot_aire_tumeur.html'))  # Sauvegarde du graphique
