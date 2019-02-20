# -*- coding: utf-8 -*-
import io
import json
import numpy as np
from PIL import Image
from flask import Flask, request, jsonify
from tensorflow import keras
import tensorflow as tf
import base64
from keras import backend as K
from keras.models import load_model
import cv2
import matplotlib
from io import BytesIO



app = Flask(__name__)
# 首先将model定义为None
model = None

def load_aimodel():
    # load the pre-trained Keras model (here we are using a model
    # pre-trained on ImageNet and provided by Keras, but you can
    # substitute in your own networks just as easily)
    global model
    model = load_model('./databest.h5')
    model._make_predict_function()


def prepare_image(image):
    # if the image mode is not RGB, convert it
    # if image.mode != "RGB":
    #     image = image.convert("RGB")
    # resize the input image and preprocess it

	trainHeight=300
	trainWidth=300
	img=base64_image(image)
	img = cv2.resize(img,(trainHeight,trainWidth))
	img=img.reshape((1,trainHeight,trainWidth,3))
	return img

def base64_image(base64_code):
		# print(base64_code)
		img_data = base64.b64decode(base64_code)
		# print(img_data)
		img_array = np.fromstring(img_data, np.uint8)
		# print(img_array)
		img = cv2.imdecode(img_array, cv2.COLOR_RGB2BGR)
		print('success to cvobject')
		return img



@app.route("/predict", methods=["POST"])
def predict():
    # initialize the data dictionary that will be returned from the
    # view
	data = {"success": False}
    # ensure an image was properly uploaded to our endpoint
	if request.method == "POST":
		datares=json.loads(request.get_data())
		# print(data)
		# data=json.loads(request.form.get('image'))
		# print(data['image'])
		img=prepare_image(datares['image'])
	
		ret=model.predict(img*(1./255))
		print(ret)
		data['success'] = True
		data['ret'] = ret.tolist()
		return jsonify(data)

      
    #         data["predictions"] = []
    #         data["success"] = True

    # # return the data dictionary as a JSON response
    # return jsonify(data)

# if this is the main thread of execution first load the model and
# then start the server
if __name__ == "__main__":
    print(("* Loading Keras model and Flask starting server..."
        "please wait until server has fully started"))
    load_aimodel()
    app.run()