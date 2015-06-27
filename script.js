var objects = [],
	svgContainer = document.getElementById("svg-container"),
	background = document.getElementById("background"),
	roomImg = 'svg/room.svg';

var actions = {
	takeButton: document.getElementById('action-take'),
	leaveButton: document.getElementById('action-leave'),
	activateButton: function (button) {
		button.classList.add('action-button-visible');
	},
	deactivateButton: function (button) {
		button.classList.remove('action-button-visible');	
	},
	deactivateAllButtons: function () {
		var buttons = document.querySelectorAll('.action-button');	
		[].forEach.call(buttons, function (button) {
			button.classList.remove('action-button-visible');
		});
	},
	initTakeButton: function () {
		this.takeButton.addEventListener('click', function () {
			var selectedObject = document.querySelector('.selected');
			actions.take(selectedObject);
		});
	},
	initLeaveButton: function () {
		this.leaveButton.addEventListener('click', function () {
			actions.leave(inventory.selectedItem());
		});
	},
	initResetButton: function () {
		document.getElementById('reset-objects').addEventListener('click', function() {
			actions.reset();
		});
	},
	take: function(object) {
		hideObject(object);
		inventory.save(object);
		this.deactivateAllButtons();
	},
	leave: function(inventoryObject) {
		var objectRef = inventoryObject.getAttribute('data-object-reference'),
			object = document.getElementById(objectRef);
		
		showObject(object);
		inventory.remove(inventoryObject);
		this.deactivateAllButtons();
	},
	reset: function () {
		localStorage.clear();

		objects.forEach(function(object) {
			showObject(object);
		});
		
		inventory.clear();
		this.deactivateAllButtons();
	}
};

var inventory = {
	itemContainer: document.getElementById("inventory-items"),
	items: document.getElementById("inventory-items").childNodes,
	selectedItem: function () {
		return document.querySelector('#inventory-items .selected');
	},
	select: function (object) {
		this.deselectAll();
		object.parentNode.classList.add('selected');
		actions.deactivateAllButtons();
		actions.activateButton(actions.leaveButton);
	},
	deselectAll: function () {
		[].forEach.call(this.items, function (item) {
			item.classList.remove('selected');
		});
	},
	save: function (object) {
		var newSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg"),
			image = object.cloneNode(true);
			
		image.setAttribute('x', 0);
		image.setAttribute('y', 0);
		newSvg.setAttribute('data-object-reference', object.id);
		newSvg.id = 'object-reference-' + object.id;
	
		newSvg.addEventListener('click', function() {
			//Show take button
			inventory.select(image);
		});
	
		setTimeout(function() {
			//Fade out
			image.classList.remove('invisible');
		}, 100);
		
		newSvg.appendChild(image);
		this.itemContainer.appendChild(newSvg);
	},
	remove: function (object) {
		var inventoryObject = document.getElementById(object.id);
		
		inventoryObject.classList.add('invisible');
		
		setTimeout(function() {
			//Fade out
			inventory.itemContainer.removeChild(inventoryObject);
		}, 100);
	},
	clear: function (object) {
		this.itemContainer.innerHTML = "";
	}
};

actions.initTakeButton();
actions.initLeaveButton();
actions.initResetButton();

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
	actions.deactivateAllButtons();
	
	objects.forEach(function(object) {
		object.classList.remove('selected');
	});
};

var selectObject = function (object) {
	object.classList.add('selected');

	actions.deactivateAllButtons();

	if (object.getAttribute('data-action-take') == "true") {
		actions.activateButton(actions.takeButton);
	}
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
			
			document.getElementById('background').addEventListener('click', function() {
				deselectAllObjects();
			});
			
			if (localStorage.getItem(object.id) == "invisible") {
				hideObject(object);
				inventory.save(object);
			}
			
			object.addEventListener('click', function() {
				deselectAllObjects();
				selectObject(object);
			});
		});
	};
})();