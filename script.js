var objects = [],
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
	image.classList.remove('invisible');
	
	svg.appendChild(image);
	inventory.appendChild(svg);
};

var clearInventory = function (object) {
	var inventory = document.getElementById('inventory');

	inventory.innerHTML = "";
};

(function loadSvg() {
	var ajax = new XMLHttpRequest();
	
	ajax.open("GET", roomImg, true);
	ajax.send();
	ajax.onload = function(e) {
		var div = document.getElementById("svg-container");
		div.innerHTML = ajax.responseText;
		
		objects = getObjects();
		
		[].forEach.call(objects, function(object) {
			object.classList.add('object');
			
			if (localStorage.getItem(object.id) == "invisible") {
				object.classList.add('invisible');
				saveToInventory(object);
			}
			
			object.addEventListener('click', function() {
				object.classList.add("invisible");
				saveToInventory(object);
				localStorage.setItem(object.id, "invisible");
			});
		});
	};
})();

(function initResetButton() {
	document.getElementById('reset-objects').addEventListener('click', function() {
		localStorage.clear();

		[].forEach.call(objects, function(object) {
			object.classList.remove("invisible");
		});
		
		clearInventory();
	});
})();