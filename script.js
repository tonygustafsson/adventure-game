var objects = [],
	svgContainer = document.getElementById("svg-container"),
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
	var inventory = document.getElementById('inventory'),
		svg = document.createElementNS("http://www.w3.org/2000/svg", "svg"),
		image = object.cloneNode(true);
		
	image.setAttribute('x', 0);
	image.setAttribute('y', 0);
	image.setAttribute('data-object-reference', object.id);
	image.id = 'object-reference-' + object.id;
	image.classList.remove('invisible');
	
	svg.addEventListener('click', function() {
		//Remove from inventory on click
		var svgObject = document.getElementById(image.getAttribute('data-object-reference'));
		removeFromInventory(object);
		showObject(svgObject);
	});
	
	svg.appendChild(image);
	inventory.appendChild(svg);
};

var removeFromInventory = function (object) {
	var inventory = document.getElementById('inventory'),
		inventoryObject = document.getElementById('object-reference-' + object.id);
	
	inventory.removeChild(inventoryObject.parentNode);
};

var clearInventory = function (object) {
	var inventory = document.getElementById('inventory');

	inventory.innerHTML = "";
};

var hideObject = function (object) {
	object.classList.add('invisible');
	localStorage.setItem(object.id, "invisible");
};

var showObject = function (object) {
	object.classList.remove('invisible');
	localStorage.removeItem(object.id);
};

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
			
			if (localStorage.getItem(object.id) == "invisible") {
				hideObject(object);
				saveToInventory(object);
			}
			
			object.addEventListener('click', function() {
				hideObject(object);
				saveToInventory(object);
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