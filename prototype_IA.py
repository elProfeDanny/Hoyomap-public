# hoyomap_ia_basica.py - Prototipo IA visión computarizada
# Danny Francisco Parada Jara - Noviembre 2025

import cv2
import numpy as np

def detectar_bache(imagen_path):
    img = cv2.imread(imagen_path)
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    blur = cv2.GaussianBlur(gray, (5,5), 0)
    edges = cv2.Canny(blur, 50, 150)
    
    # Simulación simple de detección de grietas/baches
    contornos, _ = cv2.findContours(edges, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    severidad = len(contornos) / 100  # mockup de severidad
    
    print(f"Bache detectado - Severidad: {min(severidad, 5):.1f}/5")
    return severidad

# Prueba con foto de reporte real
detectar_bache("reportes_ejemplo/bache_maipu_001.jpg")