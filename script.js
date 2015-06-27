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
			var selectedItem = document.querySelector('.selected');
			actions.take(selectedItem);
		});
	},
	initLeaveButton: function () {
		this.leaveButton.addEventListener('click', function () {
			actions.leave(inventory.selectedItem());
		});
	},
	initResetButton: function () {
		document.getElementById('reset-game').addEventListener('click', function() {
			actions.reset();
		});
	},
	take: function(item) {
		room.hideItem(item);
		inventory.save(item);
		this.deactivateAllButtons();
	},
	leave: function(inventoryItem) {
		var itemRef = inventoryItem.getAttribute('data-item-reference'),
			item = document.getElementById(itemRef);
		
		room.showItem(item);
		inventory.remove(inventoryItem);
		this.deactivateAllButtons();
	},
	reset: function () {
		localStorage.clear();

		room.items.forEach(function(item) {
			room.showItem(item);
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
	select: function (item) {
		this.deselectAll();
		item.parentNode.classList.add('selected');
		actions.deactivateAllButtons();
		actions.activateButton(actions.leaveButton);
	},
	deselectAll: function () {
		[].forEach.call(this.items, function (item) {
			item.classList.remove('selected');
		});
	},
	save: function (item) {
		var newSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg"),
			image = item.cloneNode(true);
			
		image.setAttribute('x', 0);
		image.setAttribute('y', 0);
		newSvg.setAttribute('data-item-reference', item.id);
		newSvg.id = 'item-reference-' + item.id;
	
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
	remove: function (item) {
		var inventoryItem = document.getElementById(item.id);
		
		inventoryItem.classList.add('invisible');
		
		setTimeout(function() {
			//Fade out
			inventory.itemContainer.removeChild(inventoryItem);
		}, 100);
	},
	clear: function () {
		this.itemContainer.innerHTML = "";
	}
};

var room = {
	background: function () {
		return document.getElementById("background");
	},
	container: function () {
		return document.getElementById("svg-container");
	},
	image: "svg/room.svg",
	items: [],
	getItems: function () {
		var imageItems = document.querySelectorAll('#svg-container image'),
			itemList = [];
			
		[].forEach.call(imageItems, function(item) {
			if (item.id != "background") {
				itemList.push(item);
			}
		});
		
		return itemList;
	},
	selectItem: function (item) {
		item.classList.add('selected');
		actions.deactivateAllButtons();
	
		if (item.getAttribute('data-action-take') == "true") {
			actions.activateButton(actions.takeButton);
		}
	},
	deselectItem: function (item) {
		item.classList.remove('selected');
	},
	deselectAllItems: function () {
		actions.deactivateAllButtons();
		
		room.items.forEach(function(item) {
			item.classList.remove('selected');
		});
	},
	showItem: function (item) {
		item.classList.remove('invisible');
		localStorage.removeItem(item.id);
	},
	hideItem: function (item) {
		this.deselectItem(item);
		item.classList.add('invisible');
		localStorage.setItem(item.id, "invisible");
	},
	load: function () {
		var ajax = new XMLHttpRequest();
		
		ajax.open("GET", room.image, true);
		ajax.send();
		ajax.onload = function(e) {
			room.container().innerHTML = ajax.responseText;
			room.items = room.getItems();
			
			room.items.forEach(function(item) {
				item.classList.add('item');
				
				room.background().addEventListener('click', function() {
					room.deselectAllItems();
				});
				
				if (localStorage.getItem(item.id) == "invisible") {
					room.hideItem(item);
					inventory.save(item);
				}
				
				item.addEventListener('click', function() {
					room.deselectAllItems();
					room.selectItem(item);
				});
			});
		};
	}
};

(function initialize() {
	room.load();
	actions.initTakeButton();
	actions.initLeaveButton();
	actions.initResetButton();
})();