var game = (function () {
	"use strict";
	
	var actions = {
		takeButton: document.getElementById('action-take'),
		leaveButton: document.getElementById('action-leave'),
		useButton: document.getElementById('action-use'),
		smellButton: document.getElementById('action-smell'),
		activateButton: function activateButton (button) {
			button.classList.add('action-button-visible');
		},
		deactivateButton: function deactivateButton (button) {
			button.classList.remove('action-button-visible');	
		},
		deactivateAllButtons: function deactivateAllButtons () {
			var buttons = document.querySelectorAll('.action-button');	
			[].forEach.call(buttons, function (button) {
				button.classList.remove('action-button-visible');
			});
		},
		initTakeButton: function initTakeButton () {
			actions.takeButton.addEventListener('click', function () {
				actions.take(room.selectedItem());
			});
		},
		initLeaveButton: function initLeaveButton () {
			actions.leaveButton.addEventListener('click', function () {
				actions.leave(inventory.selectedItem());
			});
		},
		initUseButton: function initUseButton () {
			actions.useButton.addEventListener('click', function () {
				actions.use(room.selectedItem());
			});
		},
		initSmellButton: function initSmellButton () {
			actions.smellButton.addEventListener('click', function () {
				actions.smell(room.selectedItem());
			});
		},
		initResetButton: function initResetButton () {
			document.getElementById('reset-game').addEventListener('click', function() {
				actions.reset();
			});
		},
		take: function take (item) {
			room.hideItem(item);
			inventory.save(item);
			actions.deactivateAllButtons();
		},
		leave: function leave (item) {
			var inventoryItem = document.getElementById('item-reference-' + item.element().id);
			
			room.showItem(item);
			inventory.remove(inventoryItem);
			actions.deactivateAllButtons();
		},
		use: function use (item) {
			room.message.add(item.useMessage);
		},
		smell: function smell (item) {
			room.message.add(item.smellMessage);
		},
		reset: function reset () {
			localStorage.clear();
	
			for (var item in items) {
				if (items.hasOwnProperty(item)) {
					room.showItem(items[item]);
				}
			}
			
			inventory.clear();
			actions.deactivateAllButtons();
		}
	};
	
	var inventory = {
		itemContainer: document.getElementById("inventory-items"),
		items: document.getElementById("inventory-items").childNodes,
		selectedItem: function selectedItem () {
			var element = document.querySelector('#inventory-items .selected');
			return room.getItemFromElement(element);
		},
		getItemElement: function getItemInventoryElement (item) {
			var elementId = "item-reference-" + item.id;
			return document.getElementById(elementId);	
		},
		select: function select (item) {
			var inventoryElement = inventory.getItemElement(item);
			inventory.deselectAll();
			inventoryElement.classList.add('selected');
			
			room.deselectAllItems();
			room.description.hide();
			
			actions.deactivateAllButtons();
			actions.activateButton(actions.leaveButton);
		},
		deselectAll: function deselectAll () {
			[].forEach.call(inventory.items, function (item) {
				item.classList.remove('selected');
			});
		},
		save: function save (item) {
			var newImage = document.createElement('img');
			newImage.id = 'item-reference-' + item.id;
			newImage.setAttribute('width', "200");
			newImage.setAttribute('height', "200");
			newImage.setAttribute('src', item.image);
			newImage.classList.add('inventory-item');
			newImage.classList.add('invisible');
			newImage.setAttribute('data-item-reference', item.id);
		
			newImage.addEventListener('click', function() {
				//Show take button
				inventory.select(item);
			});
		
			setTimeout(function() {
				//Fade in
				newImage.classList.remove('invisible');
			}, 250);
			
			inventory.itemContainer.appendChild(newImage);
		},
		remove: function remove (itemElement) {
			inventory.deselectAll();
			itemElement.classList.add('invisible');
			
			setTimeout(function() {
				//Fade out
				inventory.itemContainer.removeChild(itemElement);
			}, 250);
		},
		clear: function clear () {
			inventory.itemContainer.innerHTML = "";
		}
	};
	
	var room = {
		background: function background () {
			return document.getElementById("background");
		},
		container: function  container () {
			return document.getElementById("game");
		},
		image: "svg/room.svg",
		selectedItem: function selectedItem () {
			var element = room.container().querySelector('.selected');
			return room.getItemFromElement(element);
		},
		getItemElement: function getItemElement (item) {
			var elementId = item.id;
			return document.getElementById(elementId);	
		},
		getItemFromElement: function getItemFromElement(element) {
			var foundElement;
			
			for (var item in items) {
				if (element.id === items[item].element().id || element.getAttribute('data-item-reference') === items[item].element().id) {
					foundElement = items[item];
				}
			}
			
			return foundElement;
		},
		selectItem: function selectItem (item) {
			room.deselectAllItems();
			actions.deactivateAllButtons();
			inventory.deselectAll();
			
			if (item.element().id !== "background") {
				item.element().classList.add('selected');
			}
		
			if (item.takable) {
				actions.activateButton(actions.takeButton);
			}
			if (item.usable) {
				actions.activateButton(actions.useButton);
			}
			if (item.smellable) {
				actions.activateButton(actions.smellButton);
			}
			
			room.description.add(item);
		},
		deselectItem: function deselectItem (item) {
			item.element().classList.remove('selected');
		},
		deselectAllItems: function deselectAllItems () {
			actions.deactivateAllButtons();
			
			for (var item in items) {
				if (items.hasOwnProperty(item)) {
					items[item].element().classList.remove('selected');
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
				room.description.show();
				
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
			item.element().classList.remove('invisible');
			localStorage.removeItem(item.element().id);
		},
		hideItem: function hideItem (item) {
			room.deselectItem(item);
			item.element().classList.add('invisible');
			localStorage.setItem(item.element().id, "invisible");
		},
		createItems: function createItems () {
			var group = document.getElementsByTagName('g')[0];
			
			for (var item in items) {
				if (items.hasOwnProperty(item)) {
					var newItem = document.createElementNS('http://www.w3.org/2000/svg','image');
					item = items[item];
						
					newItem.id = item.id;
					newItem.setAttribute('width', item.width);
					newItem.setAttribute('height', item.height);
					newItem.setAttribute('x', item.x);
					newItem.setAttribute('y', item.y);
					newItem.setAttributeNS('http://www.w3.org/1999/xlink','xlink:href', item.image);
					newItem.setAttribute('data-title', item.title);
					newItem.setAttribute('data-description', item.description);
					newItem.setAttribute('data-takable', item.takable ? "true" : "false");
					newItem.classList.add('item');
					
					(function(item) {
						newItem.addEventListener('click', function () {
							room.selectItem(item);
						});
					})(item);
				
					if (localStorage.getItem(item.id) === "invisible") {
						newItem.classList.add('invisible');
						inventory.save(item);
					}
	
					group.appendChild(newItem);
				}
			}
		},
		load: function load () {
			room.createItems();
					
			//Add title and description
			var clickEvent = new Event('click');
			room.background().dispatchEvent(clickEvent);
		}
	};	
	
	//Make some stuff happen
	room.load();
	actions.initTakeButton();
	actions.initLeaveButton();
	actions.initUseButton();
	actions.initSmellButton();
	actions.initResetButton();
}());