var objects = [],
	svgContainer = document.getElementById("svg-container"),
	background = document.getElementById("background"),
	inventoryItems = document.getElementById("inventory-items"),
	roomImg = 'svg/room.svg';

var getObjects = function () {
	var imageItems = document.getElementsByTagName('image'),
		objects = [];
		
	[].forEach.call(imageItems, function(object) {
		if (object.id != "background") {
			objects.push(object);
		}
	});
	
	return objects;
};

var saveToInventory = function (object) {
	var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg"),
		image = object.cloneNode(true);
		
	image.setAttribute('x', 0);
	image.setAttribute('y', 0);
	image.setAttribute('data-object-reference', object.id);
	image.id = 'object-reference-' + object.id;

	svg.addEventListener('click', function() {
		//Show take button
		selectInventoryObject(image);
	});

	setTimeout(function() {
		//Fade out
		image.classList.remove('invisible');
	}, 100);
	
	svg.appendChild(image);
	inventoryItems.appendChild(svg);
};

var removeFromInventory = function (object) {
	var inventoryObject = document.getElementById(object.id);
	
	inventoryObject.classList.add('invisible');
	
	setTimeout(function() {
		//Fade out
		inventoryItems.removeChild(inventoryObject.parentNode);
	}, 100);
};

var clearInventory = function (object) {
	inventoryItems.innerHTML = "";
};

var hideObject = function (object) {
	object.classList.remove('selected');
	object.classList.add('invisible');
	localStorage.setItem(object.id, "invisible");
};

var showObject = function (object) {
	object.classList.remove('invisible');
	localStorage.removeItem(object.id);
};

var deselectAllObjects = function () {
	objects.forEach(function(object) {
		object.classList.remove('selected');
		document.getElementById('action-take').classList.remove('action-button-visible');
	});
};

var selectObject = function (object) {
	object.classList.add('selected');
	document.getElementById('action-take').classList.add('action-button-visible');
	document.getElementById('action-leave').classList.remove('action-button-visible');
};

var selectInventoryObject = function (object) {
	object.classList.add('selected');
	document.getElementById('action-leave').classList.add('action-button-visible');
	document.getElementById('action-take').classList.remove('action-button-visible');
};

(function initTakeButton() {
	var takeButton = document.getElementById('action-take');
	
	takeButton.addEventListener('click', function () {
		var selectedObject = document.querySelector('.selected');
		hideObject(selectedObject);
		saveToInventory(selectedObject);
		takeButton.classList.remove('action-button-visible');
	});
})();

(function initLeaveButton() {
	var leaveButton = document.getElementById('action-leave');
	
	leaveButton.addEventListener('click', function () {
		var selectedObject = document.querySelector('#inventory-items .selected'),
			svgObject = document.getElementById(selectedObject.getAttribute('data-object-reference'));
			
		showObject(svgObject);
		removeFromInventory(selectedObject);
		leaveButton.classList.remove('action-button-visible');
	});
})();

(function loadSvg() {
	var ajax = new XMLHttpRequest();
	
	ajax.open("GET", roomImg, true);
	ajax.send();
	ajax.onload = function(e) {
		var div = svgContainer;
		div.innerHTML = ajax.responseText;
		
		objects = getObjects();
		
		objects.forEach(function(object) {
			object.classList.add('object');
			
			document.getElementById('background').addEventListener('click', function() {
				deselectAllObjects();
			});
			
			if (localStorage.getItem(object.id) == "invisible") {
				hideObject(object);
				saveToInventory(object);
			}
			
			object.addEventListener('click', function() {
				deselectAllObjects();
				selectObject(object);
			});
		});
	};
})();

(function initResetButton() {
	document.getElementById('reset-objects').addEventListener('click', function() {
		localStorage.clear();

		objects.forEach(function(object) {
				showObject(object);
		});
		
		clearInventory();
	});
})();