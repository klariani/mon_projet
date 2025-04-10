import pandas as pd
import plotly.express as px
import mysql.connector

# Connexion à ta base
conn = mysql.connector.connect(
    host="localhost",
    port=3306,
    user="root",
    password="root",
    database="cancer"
)

# Récupération des données
query = """
SELECT 
    rayon_moyen,
    texture_moyenne,
    perimetre_moyen,
    air_moyenne,
    uniformite_moyenne,
    compact_moyen,
    concavite_moyenne,
    nconcavite_moyenne,
    symetrie_moyenne,
    dim_fractal_moyenne
FROM tumeur
"""
df = pd.read_sql(query, conn)
conn.close()

# Calcul de la matrice de corrélation
corr_matrix = df.corr().round(2)

# Pour plotly, on passe la matrice en format "long" (melted)
corr_melted = corr_matrix.reset_index().melt(id_vars="index")
corr_melted.columns = ["Variable1", "Variable2", "Corrélation"]

# Création du heatmap interactif
fig = px.imshow(
    corr_matrix,
    text_auto=True,
    color_continuous_scale="RdBu",
    zmin=-1,
    zmax=1,
    labels=dict(x="Variable", y="Variable", color="Corrélation"),
    title="Heatmap"
)

# Sauvegarde en HTML
fig.write_html("heatmap_interactive.html")

# Affichage en local
fig.show()
