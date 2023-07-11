/*
Copyright 2017 Google Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

'use strict';

var videoElement;
var videoSelect;
var img;
var imageCapture;
var takePhotoButton;

function getDevices() {
  // AFAICT in Safari this only gets default devices until gUM is called :/
  return navigator.mediaDevices.enumerateDevices();
}

function gotDevices(deviceInfos) {
  window.deviceInfos = deviceInfos; // make available to console
  for (const deviceInfo of deviceInfos) {
    const option = document.createElement('option');
    option.value = deviceInfo.deviceId;
    if (deviceInfo.kind === 'videoinput') {
      option.text = deviceInfo.label || `Camera ${videoSelect.length + 1}`;
      videoSelect.appendChild(option);
    }
  }
}

function getStream() {
  videoElement = document.querySelector('#modal1').classList.contains('open') ? document.querySelector('video#video') : document.querySelector('video#video1');
  videoSelect = document.querySelector('#modal1').classList.contains('open') ? document.querySelector('select#videoSource') : document.querySelector('select#videoSource1');
  img = document.querySelector('#modal1').classList.contains('open') ? document.querySelector('img#previewImage') : document.querySelector('img#previewImage1');
  takePhotoButton = document.querySelector('#modal1').classList.contains('open') ? document.querySelector('a#takePhoto') : document.querySelector('a#takePhoto1');
  takePhotoButton.onclick = takePhoto;
  videoSelect.onchange = getStream;
  if (window.stream) {
    window.stream.getTracks().forEach(track => {
      track.stop();
    });
  }
  const videoSource = videoSelect.value;
  const constraints = {
    video: {deviceId: videoSource ? {exact: videoSource} : undefined}
  };
  return navigator.mediaDevices.getUserMedia(constraints).
    then(gotStream).catch(handleError);
}

function gotStream(stream) {
  window.stream = stream; // make stream available to console
  imageCapture = new ImageCapture(stream.getVideoTracks()[0]);
  videoSelect.selectedIndex = [...videoSelect.options].
    findIndex(option => option.text === stream.getVideoTracks()[0].label);
  videoElement.srcObject = stream;
}

function handleError(error) {
  console.error('Error: ', error);
}

function takePhoto() {
    imageCapture.takePhoto().then(function(blob) {
        img.classList.remove('hidden');
        var reader = new FileReader();
        reader.readAsDataURL(blob); 
        reader.onloadend = function() {
          var base64data = reader.result;                
          img.src = base64data;
        }
    }).catch(function(error) {
        console.log('takePhoto() error: ', error);
    });
}