var objects = [],
	svgContainer = document.getElementById("svg-container"),
	background = document.getElementById("background"),
	inventoryItems = document.getElementById("inventory-items"),
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
			var inventoryObject = document.querySelector('#inventory-items .selected'),
				objectRef = inventoryObject.getAttribute('data-object-reference'),
				object = document.getElementById(objectRef);
						
			actions.leave(inventoryObject, object);
		});
	},
	initResetButton: function () {
		document.getElementById('reset-objects').addEventListener('click', function() {
			actions.reset();
		});
	},
	take: function(object) {
		hideObject(object);
		saveToInventory(object);
		this.deactivateAllButtons();
	},
	leave: function(inventoryObject, object) {
		showObject(object);
		removeFromInventory(inventoryObject);
		this.deactivateAllButtons();
	},
	reset: function () {
		localStorage.clear();

		objects.forEach(function(object) {
			showObject(object);
		});
		
		clearInventory();
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

var selectInventoryObject = function (object) {
	object.classList.add('selected');
	actions.deactivateAllButtons();
	actions.activateButton(actions.leaveButton);
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
				saveToInventory(object);
			}
			
			object.addEventListener('click', function() {
				deselectAllObjects();
				selectObject(object);
			});
		});
	};
})();