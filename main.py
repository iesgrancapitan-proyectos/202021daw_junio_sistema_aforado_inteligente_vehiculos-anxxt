import cv2
import imutils
import numpy as np
import pytesseract
import binascii
from Crypto import Random
from Crypto.Cipher import AES
import json
import base64
import requests

# Función de encriptación
def encriptar(data, passphrase):
	"""
		 Cifrado usando AES-256-CBC con iv aleatorio / compartido
		'passphrase' debe estar en hexadecimal, generar con 'openssl rand -hex 32'
	"""
	try:
		key = binascii.unhexlify(passphrase)
		pad = lambda s : s+chr(16-len(s)%16)*(16-len(s)%16)
		iv = Random.get_random_bytes(16)
		cipher = AES.new(key, AES.MODE_CBC, iv)
		encrypted_64 = base64.b64encode(cipher.encrypt(pad(data))).decode('ascii')
		iv_64 = base64.b64encode(iv).decode('ascii')
		json_data = {}
		json_data['iv'] = iv_64
		json_data['data'] = encrypted_64
		clean = base64.b64encode(json.dumps(json_data).encode('ascii'))
	except Exception as e:
		print("Cannot encrypt data...")
		print(e)
		exit(1)
	return clean

def enviar_datos(data):
	url = "https://proyectointegrado2021.000webhostapp.com/insert.php"
	datos = {"data": data} # El json que enviamos
	respuesta = requests.post(url, json=datos)

	# Ahora decodificamos la respuesta como json
	print(respuesta.content)

# Inicializar la webcam, cap es el objeto proporcionado por la captura de video
# contiene un booleano que indica si fue exitoso (ret)
# también contiene las imágenes recopiladas de la webcam (frame)

cap = cv2.VideoCapture(0)

limit = 0
passphrase = "822e130304addcbd3852619ccdbdd1a81152b353ffc4d0e69ed38787e37f22bb"

while True:

	ret,frame = cap.read()

	frame = cv2.resize(frame, (640,480) )

	#frame = cv2.addWeighted(frame, 0.5, np.zeros(frame.shape, frame.dtype), 0, 0) # Por si hay que ajustar el contraste

	gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY) # Convertir a escala de grises
	canny = cv2.Canny(gray, 150, 300) # Realizar detección de bordes
	canny = cv2.dilate(canny, None, iterations=1) # Hace que los bordes sean más gruesos
	canny = cv2.erode(canny, None, iterations=1) # Hace que los bordes sean menos gruesos

	# Encuentra contornos en la imagen procesada para la detección de bordes y mantiene los que están dentro del rango que le he dado
	# e inicializa nuestro contorno
	cnts = cv2.findContours(canny, cv2.RETR_TREE, cv2.CHAIN_APPROX_SIMPLE)
	cnts = imutils.grab_contours(cnts)
	cnts = sorted(cnts, key = cv2.contourArea, reverse = True)[:10]
	screenCnt = None

	# Recorre los contornos que ha detectado previamente
	for c in cnts:
		# Le damos unos parámetros para que pueda detectar aproximadamente la matrícula		
		peri = cv2.arcLength(c, True)
		approx = cv2.approxPolyDP(c, 0.018 * peri, True)
	
		x,y,w,h = cv2.boundingRect(approx)

		# Si nuestro contorno aproximado tiene cuatro puntos, entonces
		# podemos asumir que hemos encontrado la matrícula
		if len(approx) == 4:
			screenCnt = approx
			aspect_ratio = round(float(w)/h)

			if aspect_ratio >= 4:
				cv2.putText(frame,'Matricula', (x,y-5),1,1.5,(0,255,0),2)

				if screenCnt is None:
					detected = 0
				else:
					detected = 1

				if detected == 1:
					cv2.drawContours(frame, [screenCnt], -1, (0, 255, 0), 2)
					
					if limit == 50:
						mask = np.zeros(gray.shape,np.uint8)
						new_image = cv2.drawContours(mask,[screenCnt],0,255,-1,)
						new_image = cv2.bitwise_and(frame,frame,mask=mask)

						# Cambiar la perspectiva
						pts1 = np.float32(screenCnt)

						# Primer valor: Array con un array de dos valores, Segundo valor: Un array con dos valores, Tercer valor: El valor 0 del array
						if (screenCnt[0][0][0] < screenCnt[2][0][0]):
							pts2 = np.float32([[0,0], [0,50], [220,50], [220,0]]) # Desde la izquierda
						else:
							pts2 = np.float32([[220,0], [0,0], [0,50], [220,50]]) # Desde la derecha

						M = cv2.getPerspectiveTransform(pts1, pts2)
						dst = cv2.warpPerspective(new_image, M, (220,50))

						cropped = dst[0:50,23:217] # Recortar para quitar cosas que pueden interferir

						cropped_gray = cv2.cvtColor(cropped, cv2.COLOR_BGR2GRAY) # Convertir a escala de grises

						# Leer la matrícula
						text = pytesseract.image_to_string(cropped_gray, config='--oem 3 --psm 7')
						text = text.strip().upper()
						
						if len(text) == 8:
							print("Matrícula:",text)
							enviar_datos(encriptar(text, passphrase))
						
						limit = 0

					limit += 1
			break

	cv2.imshow('Detección de matrículas', frame)

	if cv2.waitKey(1) == 13: #13 es la tecla intro
		break

# Libera la cámara y cierra la ventana
cap.release()
cv2.destroyAllWindows()