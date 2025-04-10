import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler
from scipy.spatial import Voronoi, voronoi_plot_2d
import mysql.connector
import mpld3  # Importer la bibliothèque mpld3

# Connexion à la base de données MySQL
conn = mysql.connector.connect(
    host="localhost",
    port="3306",  # Changez selon votre configuration
    user="root",
    password="root",
    database="cancer"
)

# Requête SQL avec jointure entre les entités tumeur et diagnostic
query = """
SELECT 
    t.rayon_moyen,
    t.texture_moyenne,
    t.perimetre_moyen,
    t.air_moyenne,
    t.uniformite_moyenne,
    t.compact_moyen,
    t.concavite_moyenne,
    t.nconcavite_moyenne,
    t.symetrie_moyenne,
    t.dim_fractal_moyenne,
    d.libelle_diagnostic
FROM tumeur t
JOIN diagnostic d ON t.code_diagnostic = d.code_diagnostic
"""

# Charger les données dans un DataFrame
df = pd.read_sql(query, conn)

# Fermer la connexion
conn.close()

# Sélectionner les colonnes d'intérêt (caractéristiques)
features = ["rayon_moyen", "texture_moyenne", "perimetre_moyen", "air_moyenne",
            "uniformite_moyenne", "compact_moyen", "concavite_moyenne",
            "nconcavite_moyenne", "symetrie_moyenne", "dim_fractal_moyenne"]

# Extraire les données des caractéristiques
X = df[features]

# Standardiser les données
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Effectuer PCA
pca = PCA(n_components=2)
X_pca = pca.fit_transform(X_scaled)

# Ajouter les résultats PCA dans le DataFrame pour visualisation
df_pca = pd.DataFrame(X_pca, columns=['PC1', 'PC2'])
df_pca['diagnostic'] = df['libelle_diagnostic']  # La colonne contenant les labels B/M (diagnostic)

# Créer une instance de Voronoi pour les 2 premières composantes principales
vor = Voronoi(X_pca)

# Tracer le diagramme de Voronoi avec les points projetés
fig, ax = plt.subplots(figsize=(10, 7))

# Tracer le diagramme de Voronoi
voronoi_plot_2d(vor, show_vertices=False, line_colors='orange', line_width=2, ax=ax)

# Tracer les points avec les couleurs des classes
scatter = ax.scatter(X_pca[:, 0], X_pca[:, 1], c=df_pca['diagnostic'].map({'B': 'blue', 'M': 'red'}), alpha=0.5)

# Ajouter un titre et les labels
ax.set_title('Diagramme de Voronoi sur PCA')
ax.set_xlabel('PC1')
ax.set_ylabel('PC2')

# Enregistrer le graphique dans un fichier HTML
html_str = mpld3.fig_to_html(fig)  # Convertir le graphique Matplotlib en HTML

# Enregistrer le fichier HTML
with open("voronoi_diagram.html", "w") as f:
    f.write(html_str)

print("Le graphique a été enregistré dans 'voronoi_diagram.html'")
import seaborn as sns
import matplotlib.pyplot as plt
import pandas as pd

# Supposons que X_pca et df['y'] existent
pca_df = pd.DataFrame(X_pca, columns=['PC1', 'PC2'])
pca_df['Classe'] = df['y']

plt.figure(figsize=(10, 7))
sns.scatterplot(data=pca_df, x='PC1', y='PC2', hue='Classe', palette={'B': 'blue', 'M': 'red'}, alpha=0.6)
sns.kdeplot(data=pca_df, x='PC1', y='PC2', hue='Classe', levels=1, linewidths=2, linestyle="--", alpha=0.5)
plt.title("Projection PCA avec contours de classes")
plt.xlabel("Composante principale 1")
plt.ylabel("Composante principale 2")
plt.legend(title="Classe")
plt.show()
import mpld3

# Sauvegarder la figure en HTML
html_str = mpld3.fig_to_html(plt.gcf())  # gcf() = get current figure
with open("pca_kde_plot.html", "w") as f:
    f.write(html_str)
