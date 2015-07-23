var game = (function () {
	"use strict";
	
	var actions = {
		buttons: {
			take: function take () {
				return document.getElementById('action-take');
			},
			use: function use () {
				return document.getElementById('action-use');
			},
			smell: function smell () {
				return document.getElementById('action-smell');
			},
			cancels: function cancels () {
				return document.querySelectorAll('.action-cancel-button');
			},
			leave: function leave () {
				return document.getElementById('action-leave');
			},
			reset: function reset () {
				return document.getElementById('action-reset');
			},
			activate: function activate (button) {
				button.classList.add('action-button-visible');
			},
			deactivate: function deactivate (button) {
				button.classList.remove('action-button-visible');	
			},
			deactivateAll: function deactivateAll () {
				var buttons = document.querySelectorAll('.action-button');
				
				[].forEach.call(buttons, function (button) {
					if (!button.classList.contains('persistant-button')) {
						button.classList.remove('action-button-visible');
					}
				});
			},
			initialize: function initialize () {
				actions.buttons.take().addEventListener('click', function () {
					actions.take(room.selectedItem());
				});
				actions.buttons.leave().addEventListener('click', function () {
					actions.leave(inventory.selectedItem());
				});
				actions.buttons.use().addEventListener('click', function () {
					var item = room.selectedItem() ? room.selectedItem() : inventory.selectedItem();
					actions.use(item);
				});
				actions.buttons.smell().addEventListener('click', function () {
					var item = room.selectedItem() ? room.selectedItem() : inventory.selectedItem();
					actions.smell(item);
				});
				actions.buttons.reset().addEventListener('click', function () {
					actions.reset();
				});
				
				for (var i = 0; i < actions.buttons.cancels().length; i++) {
					actions.buttons.cancels()[i].addEventListener('click', function () {
						actions.cancel();
					});
				}
			}
		},
		room: {
			activate: function activate (e) {
				e.preventDefault();
				
				var link = e.target,
					href = link.getAttribute('href'),
					wantedRoom = link.getAttribute('data-room');
					
				localStorage.setItem('room', wantedRoom);
				window.location.assign(href);
			},
			initChangeRoomsLinks: function initialize () {
				var changeRoomLinks = document.querySelectorAll('.change-room-link');
				for (var i = 0; i < changeRoomLinks.length; i++) {
					changeRoomLinks[i].addEventListener('click', actions.room.activate);
				}
			}
		},
		take: function take (item) {
			room.hideItem(item);
			inventory.save(item);
			actions.buttons.deactivateAll();
		},
		leave: function leave (item) {
			var inventoryItem = document.getElementById('item-reference-' + item.id);
			
			room.showItem(item);
			inventory.remove(inventoryItem);
			actions.buttons.deactivateAll();
		},
		use: function use (item) {
			room.message.add(item.useMessage);
		},
		smell: function smell (item) {
			room.message.add(item.smellMessage);
		},
		cancel: function cancel () {
			panels.hideAll();
			room.deselectAllItems();
			inventory.deselectAll();
		},
		reset: function reset () {
			localStorage.clear();
	
			for (var item in roomData.items) {
				if (roomData.items.hasOwnProperty(item)) {
					room.showItem(roomData.items[item]);
				}
			}
			
			inventory.clear();
			actions.buttons.deactivateAll();
			panels.hideAll();
		}
	};
	
	var inventory = {
		itemContainer: document.getElementById("inventory-items"),
		items: document.getElementById("inventory-items").childNodes,
		selectedItem: function selectedItem () {
			var element = document.querySelector('#inventory-items .selected');
			return inventory.getItemFromStorage(element.getAttribute('data-item-reference'));
		},
		getItemElement: function getItemInventoryElement (item) {
			var elementId = "item-reference-" + item.id;
			return document.getElementById(elementId);	
		},
		getItemFromStorage: function getItemFromStorage (itemId) {
			return JSON.parse(localStorage.getItem(inventory.getInventoryId(itemId)));
		},
		select: function select (item) {
			var inventoryElement = inventory.getItemElement(item);
			
			inventory.deselectAll();
			inventoryElement.classList.add('selected');
			
			room.deselectAllItems();
			room.description.add(item);
			room.description.show();
			
			panels.action.show();
			
			actions.buttons.deactivateAll();
			actions.buttons.activate(actions.buttons.leave());
			
			if (item.usable) {
				actions.buttons.activate(actions.buttons.use());
			}
			if (item.smellable) {
				actions.buttons.activate(actions.buttons.smell());
			}
		},
		deselectAll: function deselectAll () {
			[].forEach.call(inventory.items, function (item) {
				item.classList.remove('selected');
			});
		},
		getInventoryId: function getInventoryId (itemName) {
			return "item#" + document.getElementById('game').getAttribute('data-room') + "#" + itemName;
		},
		save: function save (item) {
			var newImage = document.createElement('img');
			newImage.id = 'item-reference-' + item.id;
			newImage.setAttribute('width', "200");
			newImage.setAttribute('height', "200");
			newImage.setAttribute('src', room.getItemImage(item.image, item.room));
			newImage.classList.add('inventory-item');
			newImage.classList.add('invisible');
			newImage.setAttribute('data-item-reference', item.id);
		
			newImage.addEventListener('click', function() {
				//Show take button
				inventory.select(item);
			});
			
			panels.action.hide();
		
			setTimeout(function() {
				//Fade in
				newImage.classList.remove('invisible');
			}, 250);
			
			inventory.itemContainer.appendChild(newImage);
		},
		remove: function remove (itemElement) {
			inventory.deselectAll();
			itemElement.classList.add('invisible');
			
			actions.cancel();
			
			setTimeout(function() {
				//Fade out
				inventory.itemContainer.removeChild(itemElement);
			}, 250);
		},
		clear: function clear () {
			inventory.itemContainer.innerHTML = "";
		},
		initialize: function initialize () {
			for (var i = 0; i < localStorage.length; i++) {
				if (localStorage.key(i).substring(0,5) === "item#") {
					var item = JSON.parse(localStorage.getItem(localStorage.key(i)));
					inventory.save(item);
				}
			}
		}
	};
	
	var panels = {
		hideAll: function hideAll () {
			panels.action.hide();
			panels.inventory.hide();
			panels.map.hide();
			panels.settings.hide();
		},
		action: {
			panelElement: function panelElement () {
				return document.getElementById("action-panel");
			},
			show: function show () {
				panels.action.panelElement().classList.add("visible");	
			},
			hide: function hide () {
				panels.action.panelElement().classList.remove("visible");	
			},
			toggle: function toggle () {
				if (panels.action.panelElement().classList.contains("visible")) {
					panels.action.hide();
				}
				else {
					panels.action.show();
				}
			},
			initPanel: (function initPanel () {
				document.getElementById('expand-action-panel').addEventListener('click', function (e) {
					e.preventDefault();
					panels.action.toggle();
				});
			}())
		},
		settings: {
			panelElement: function panelElement () {
				return document.getElementById("settings-panel");
			},
			show: function show () {
				panels.settings.panelElement().classList.add("visible");	
			},
			hide: function hide () {
				panels.settings.panelElement().classList.remove("visible");	
			},
			toggle: function toggle () {
				if (panels.settings.panelElement().classList.contains("visible")) {
					panels.settings.hide();
				}
				else {
					panels.settings.show();
				}
			},
			initPanel: (function initPanel () {
				document.getElementById('expand-settings-panel').addEventListener('click', function (e) {
					e.preventDefault();
					panels.action.hide();
					panels.settings.toggle();
				});
			}())
		},
		map: {
			panelElement: function panelElement () {
				return document.getElementById("map-panel");
			},
			show: function show () {
				panels.map.panelElement().classList.add("visible");	
			},
			hide: function hide () {
				panels.map.panelElement().classList.remove("visible");	
			},
			toggle: function toggle () {
				if (panels.map.panelElement().classList.contains("visible")) {
					panels.map.hide();
				}
				else {
					panels.map.show();
				}
			},
			initPanel: (function initPanel () {
				document.getElementById('expand-map-panel').addEventListener('click', function (e) {
					e.preventDefault();
					
					panels.inventory.hide();
					panels.map.toggle();
				});
			}())
		},
		inventory: {
			panelElement: function panelElement () {
				return document.getElementById("inventory-panel");
			},
			show: function show () {
				panels.inventory.panelElement().classList.add("visible");	
			},
			hide: function hide () {
				panels.inventory.panelElement().classList.remove("visible");	
			},
			toggle: function toggle () {
				if (panels.inventory.panelElement().classList.contains("visible")) {
					panels.inventory.hide();
				}
				else {
					panels.inventory.show();
				}
			},
			initPanel: (function initPanel () {
				document.getElementById('expand-inventory-panel').addEventListener('click', function (e) {
					e.preventDefault();
					
					panels.map.hide();
					panels.inventory.toggle();
				});
			}())
		}
	};
	
	var room = {
		get: function get () {
			return document.getElementById("room");
		},
		container: function container () {
			return document.getElementById("game");
		},
		image: "svg/room.svg",
		selectedItem: function selectedItem () {
			var element = room.container().querySelector('.selected'),
				foundItem;
			
			if (!element)
				return;
			
			for (var item in roomData.items) {
				if (element.id === roomData.items[item].id || element.getAttribute('data-item-reference') === roomData.items[item].id) {
					foundItem = roomData.items[item];
				}
			}
			
			return foundItem;
		},
		getItemImage: function getItemImage (image, room) {
			var itemImagesRoot = document.getElementById('game').getAttribute('data-item-images-root'),
				room = typeof room !== "undefined" ? room : document.getElementById('game').getAttribute('data-room');
			
			if (typeof image !== 'undefined' && image !== "") {
				return itemImagesRoot + '/' + room + '/' + image;
			}
			else {
				return itemImagesRoot + '/../transparent.png';
			}
		},
		selectItem: function selectItem (item) {
			room.deselectAllItems();
			actions.buttons.deactivateAll();
			inventory.deselectAll();
			document.getElementById(item.id).classList.add('selected');
			
			if (item.takable) {
				actions.buttons.activate(actions.buttons.take());
			}
			if (item.usable) {
				actions.buttons.activate(actions.buttons.use());
			}
			if (item.smellable) {
				actions.buttons.activate(actions.buttons.smell());
			}
			
			room.description.add(item);
			room.description.show();
		},
		deselectItem: function deselectItem (item) {
			document.getElementById(item.id).classList.remove('selected');
		},
		deselectAllItems: function deselectAllItems () {
			actions.buttons.deactivateAll();
			
			for (var item in roomData.items) {
				if (roomData.items.hasOwnProperty(item)) {
					document.getElementById(roomData.items[item].id).classList.remove('selected');
				}
			}
		},
		description: {
			element: function element() {
				return document.getElementById("description");	
			},
			add: function add(item) {
				room.description.reset();
				room.message.reset();
				
				if (item.title.length > 0) {
					var title = document.createElement("h2");
					title.textContent = item.title;	
					room.description.element().appendChild(title);	
				}
	
				if (item.description.length > 0) {
					var description = document.createElement("p");
					description.textContent = item.description;	
					room.description.element().appendChild(description);
				}
			},
			reset: function reset () {
				room.description.element().innerHTML = "";	
			},
			hide: function hide () {
				room.description.element().classList.add('invisible');	
			},
			show: function show () {
				panels.action.show();
				room.description.element().classList.remove('invisible');
			}	
		},
		message: {
			element: function element() {
				return document.getElementById("message");	
			},
			add: function add(message) {
				room.message.reset();
				room.message.show();
				
				if (message.length > 0) {
					var messageElement = document.createElement("p");
					messageElement.textContent = message;	
					room.message.element().appendChild(messageElement);	
				}
			},
			reset: function reset () {
				room.message.element().innerHTML = "";	
			},
			hide: function hide () {
				room.message.element().classList.add('invisible');	
			},
			show: function show () {
				room.message.element().classList.remove('invisible');	
			}	
		},
		showItem: function showItem (item) {
			document.getElementById(item.id).classList.remove('invisible');
			localStorage.removeItem(inventory.getInventoryId(item.id));
		},
		hideItem: function hideItem (item) {
			room.deselectItem(item);
			document.getElementById(item.id).classList.add('invisible');
			
			item.room = document.getElementById('game').getAttribute('data-room');
			localStorage.setItem(inventory.getInventoryId(item.id), JSON.stringify(item));
		},
		checkLocation: function checkLocation () {
			var localRoom = localStorage.getItem('room') !== null ? localStorage.getItem('room') : "beach-house",
				sessionRoom = document.getElementById('game').getAttribute('data-room');
			
			if (localRoom !== sessionRoom) {
				// Local storage room does not match session room, redirect to change session
				var url = document.getElementById('game').getAttribute('data-base-url') + '/game/change_room/' + localRoom;
				localStorage.setItem('room', localRoom);
				window.location.assign(url);
			}
			
			localStorage.setItem('room', localRoom);
		},
		createRoom: function createRoom () {
			var group = document.getElementsByTagName('g')[0],
				background = document.createElementNS('http://www.w3.org/2000/svg', 'image');
			
			background.id = 'room-background';
			background.setAttribute('width', roomData.width);
			background.setAttribute('height', roomData.height);
			background.setAttribute('x', roomData.x);
			background.setAttribute('y', roomData.y);
			background.setAttributeNS('http://www.w3.org/1999/xlink','xlink:href', room.getItemImage(roomData.image));
			background.setAttribute('data-title', roomData.title);
			background.setAttribute('data-description', roomData.description);
			background.classList.add('room-background');
			
			room.description.add(roomData);
			
			(function() {
				background.addEventListener('click', function () {
					panels.hideAll();
					inventory.deselectAll();
					room.deselectAllItems();
					
					room.description.add(roomData);
				});
			})();
			
			group.appendChild(background);
		},
		createItems: function createItems () {
			var group = document.getElementsByTagName('g')[0];
			
			for (var item in roomData.items) {
				if (roomData.items.hasOwnProperty(item)) {
					var newItem = document.createElementNS('http://www.w3.org/2000/svg','image');
					
					item = roomData.items[item];
						
					newItem.id = item.id;
					newItem.setAttribute('width', item.width);
					newItem.setAttribute('height', item.height);
					newItem.setAttribute('x', item.x);
					newItem.setAttribute('y', item.y);
					newItem.setAttributeNS('http://www.w3.org/1999/xlink','xlink:href', room.getItemImage(item.image));
					newItem.setAttribute('data-title', item.title);
					newItem.setAttribute('data-description', item.description);
					newItem.setAttribute('data-takable', item.takable ? "true" : "false");
					newItem.classList.add('item');
					newItem.classList.add('item-' + item.id);
					
					(function(item) {
						newItem.addEventListener('click', function () {
							room.selectItem(item);
						});
					})(item);
				
					var storedItem = inventory.getItemFromStorage(item.id);
				
					if (storedItem) {
						newItem.classList.add('invisible');
					}
	
					group.appendChild(newItem);
				}
			}
		},
		load: function load () {
			room.checkLocation();
			room.createRoom();
			room.createItems();
			actions.buttons.initialize();
			actions.room.initChangeRoomsLinks();
			inventory.initialize();
		}
	};	
	
	//Make some stuff happen
	room.load();
}());