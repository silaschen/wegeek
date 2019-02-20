# -*- coding: utf-8 -*-
import io

import numpy as np
from PIL import Image
from flask import Flask, request, jsonify
from tensorflow import keras
import tensorflow as tf

app = Flask(__name__)
# 首先将model定义为None，原因在后面解释。
model = None
