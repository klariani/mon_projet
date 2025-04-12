# Bibliothèques
import pandas as pd
import numpy as np
from sqlalchemy import create_engine
import pymysql
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler
import plotly.graph_objects as go
import os
import plotly.express as px

#  Définir le chemin de sauvegarde des figures HTML 
img_path = "C:/mamp/htdocs/mon_projet/img"
os.makedirs(img_path, exist_ok=True)

#  Connexion à la base de données MySQL 
user = "root"
password = "root"
host = "localhost"
database = "patients_cancer"
engine = create_engine(f"mysql+pymysql://{user}:{password}@{host}/{database}")

#  Lecture des données depuis la table "patient" 
query = "SELECT * FROM patient"
df = pd.read_sql(query, engine)
engine.dispose()

#  Vérification si la table contient des données 
if df.empty:
    print("Erreur : la table 'patient' est vide.")
    exit()

#  Ajout d’une colonne de diagnostic avec une lecture plus compréhensible 
df['Diagnostic'] = df['classification'].map({1: 'Sain', 2: 'Malade'})

#  Liste des variables à analyser 
variables = ['age', 'bmi', 'glucose', 'insulin', 'homa',
             'leptin', 'adiponectin', 'resistin', 'mcp1']

#  Séparation des données explicatives (features) et de la cible (label) 
X = df[variables]
y = df['Diagnostic']

#  Normalisation des données avant l'ACP (Analyse en Composantes Principales) 
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

#  Application de l'ACP avec 2 composantes principales 
pca = PCA(n_components=2)
X_pca = pca.fit_transform(X_scaled)

#  Construction d'un DataFrame avec les résultats de l'ACP 
df_pca = pd.DataFrame(X_pca, columns=['CP1', 'CP2'])
df_pca['Diagnostic'] = y

#  Définition des couleurs pour les différents diagnostics 
colors = {'Sain': 'rgba(0, 128, 0, 0.4)', 'Malade': 'rgba(255, 0, 0, 0.4)'}
border_colors = {'Sain': 'green', 'Malade': 'red'}

#  Création du graphique ACP avec des zones de concentration pour chaque groupe ===
fig_pca = go.Figure()

# Tracer les points pour chaque groupe (Sain, Malade)
for diag in df_pca['Diagnostic'].unique():
    subset = df_pca[df_pca['Diagnostic'] == diag]

    # Points de chaque groupe
    fig_pca.add_trace(go.Scatter(
        x=subset['CP1'],
        y=subset['CP2'],
        mode='markers',
        name=f'{diag}',
        marker=dict(size=8, opacity=0.6),
        legendgroup=diag
    ))

    # Calcul et ajout des ellipses de concentration autour de chaque groupe
    cov = np.cov(subset[['CP1', 'CP2']].values.T)
    mean = subset[['CP1', 'CP2']].mean().values
    lambda_, v = np.linalg.eig(cov)
    lambda_ = np.sqrt(lambda_)

    t = np.linspace(0, 2 * np.pi, 100)
    ellipse = np.array([lambda_[0]*np.cos(t), lambda_[1]*np.sin(t)])
    ellipse_rotated = v @ ellipse
    ellipse_points = ellipse_rotated.T + mean

    fig_pca.add_trace(go.Scatter(
        x=ellipse_points[:, 0],
        y=ellipse_points[:, 1],
        mode='lines',
        name=f"Zone {diag}",
        line=dict(color=border_colors[diag], dash='dot'),
        fill='toself',
        fillcolor=colors[diag],
        opacity=0.3,
        showlegend=False
    ))

# Mise à jour du titre et des axes du graphique ACP
fig_pca.update_layout(
    title="ACP des Patients avec Zones de Groupe",
    xaxis_title="Composante Principale 1",
    yaxis_title="Composante Principale 2",
    width=800,
    height=600,
    template="plotly_white",
    legend=dict(title="Diagnostic")
)

# Sauvegarde du graphique ACP sous format HTML
fig_pca.write_html(os.path.join(img_path, 'acp_patients_frontieres.html'))

# Création d'un Boxplot Dynamique par Variable 

# Initialisation de la figure pour le boxplot
fig_box = go.Figure()

# Affichage dynamique de chaque boxplot en fonction de la variable sélectionnée
active_index = 0  # Affichage de la première variable au début
for i, var in enumerate(variables):
    fig_box.add_trace(go.Box(
        x=df['Diagnostic'],
        y=df[var],
        name=var,
        marker_color='rgba(214, 39, 40, 0.7)',
        boxmean=True,
        visible=(i == active_index)  # Affiche uniquement la première variable sélectionnée
    ))

# Création des boutons pour sélectionner la variable à afficher
box_buttons = []
for i, var in enumerate(variables):
    visible = [j == i for j in range(len(variables))]  # Affiche uniquement la variable sélectionnée
    box_buttons.append(dict(
        label=var,
        method="update",
        args=[{"visible": visible}, {"title": f"Boxplot pour {var} par diagnostic", "yaxis": {"title": var}}]
    ))

# Mise à jour de l'apparence du graphique boxplot
fig_box.update_layout(
    updatemenus=[dict(
        buttons=box_buttons,       # Ajoute les boutons pour changer de variable
        direction="down",          # Menu déroulant
        showactive=True,           # Met en surbrillance la variable sélectionnée
        x=0.5,
        xanchor="center",
        y=1.15,
        yanchor="top"
    )],
    title="Boxplot par diagnostic",   # Titre de base
    yaxis_title="Valeur",             # Titre de l'axe Y
    xaxis_title="Diagnostic du patient",         # Axe X fixe
    template="plotly_white",          # Thème graphique
    width=800,
    height=600
)

# Sauvegarde du boxplot dynamique dans le dossier img
fig_box.write_html(os.path.join(img_path, "boxplot_par_diagnostic.html"))

# Création d'un Barplot des Corrélations entre Variables

fig_bar = go.Figure()
active_index = 0  # Affichage de la première variable par défaut

# Création des barplots pour chaque variable
for i, var in enumerate(variables):
    corr_with_var = df[variables].corr()[var].drop(var)
    fig_bar.add_trace(go.Bar(
        x=corr_with_var.index,
        y=corr_with_var.values,
        name=var,
        marker_color='royalblue',
        visible=(i == active_index)  # Affiche uniquement la première variable
    ))

# Création des boutons pour afficher chaque variable
bar_buttons = []
for i, var in enumerate(variables):
    visible = [j == i for j in range(len(variables))]
    bar_buttons.append(dict(
        label=var,
        method="update",
        args=[{"visible": visible}, {"title": {"text": f"Corrélation des variables avec : "}}]
    ))

# Mise en page générale du graphique
fig_bar.update_layout(
    updatemenus=[dict(
        buttons=bar_buttons,
        direction="down",
        showactive=True,
        x=0.5,
        xanchor="center",
        y=1.15,
        yanchor="top"
    )],
    title={"text": f"Corrélation des variables avec : "},
    xaxis_title="Variables",
    yaxis_title="Coefficient de corrélation",
    template="plotly_white",
    width=800,
    height=600
)

# Sauvegarde du barplot des corrélations
fig_bar.write_html(os.path.join(img_path, "correlation_barplot.html"))

# Création d'un graphique Sunburst des Variables par Diagnostic

# Création des labels et des parents pour le Sunburst
labels = ['Sain', 'Malade']  # Premier niveau : Diagnostic

# Ajouter les variables associées à chaque diagnostic
for diag in ['Sain', 'Malade']:
    for var in variables:
        labels.append(f"{diag} : {var}")

# Définir les parents (les diagnostics sont les racines)
parents = ['', '']
for diag in ['Sain', 'Malade']:
    for _ in variables:
        parents.append(diag)

# Calcul des moyennes des variables pour chaque diagnostic
means_sain = df[df['Diagnostic'] == 'Sain'][variables].mean().values.tolist()
means_malade = df[df['Diagnostic'] == 'Malade'][variables].mean().values.tolist()

# Création des valeurs (les moyennes des variables)
values = [0, 0]  # Les diagnostics n'ont pas de valeur spécifique ici
values += means_sain + means_malade  # Ajout des moyennes des variables

# Création du graphique Sunburst
fig_sunburst = go.Figure(go.Sunburst(
    labels=labels,
    parents=parents,
    values=values,
    hovertemplate="%{label}<br>Valeur: %{value}<extra></extra>",
))

# Mise à jour du graphique Sunburst
fig_sunburst.update_layout(
    title={'text': "Répartition des Variables Biométriques selon le Diagnostic", 'x': 0.5, 'xanchor': 'center'},
    template="plotly_white",
    width=600,  # Taille pour plus de lisibilité
    height=600,
    margin=dict(t=50, l=50, r=50, b=50)
)

# Sauvegarde du graphique Sunburst
fig_sunburst.write_html(os.path.join(img_path, "zoomable_sunburst.html"))
