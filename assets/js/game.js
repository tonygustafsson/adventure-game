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
					actions.use(room.selectedItem());
				});
				actions.buttons.smell().addEventListener('click', function () {
					actions.smell(room.selectedItem());
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
		take: function take (item) {
			room.hideItem(item);
			inventory.save(item);
			actions.buttons.deactivateAll();
		},
		leave: function leave (item) {
			var inventoryItem = document.getElementById('item-reference-' + item.element().id);
			
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
	
			for (var item in items) {
				if (items.hasOwnProperty(item)) {
					room.showItem(items[item]);
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
			return room.getItemFromElement(element);
		},
		getItemElement: function getItemInventoryElement (item) {
			var elementId = "item-reference-" + item.id;
			return document.getElementById(elementId);	
		},
		select: function select (item) {
			var inventoryElement = inventory.getItemElement(item),
				item = document.getElementById(inventoryElement.getAttribute('data-item-reference')),
				itemInfo = room.getItemFromElement(item);
			
			inventory.deselectAll();
			inventoryElement.classList.add('selected');
			
			room.deselectAllItems();
			room.description.add(itemInfo);
			
			panels.action.show();
			
			actions.buttons.deactivateAll();
			actions.buttons.activate(actions.buttons.leave());
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
			newImage.setAttribute('src', room.getItemImage(item.image));
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
		background: function background () {
			return document.getElementById("background");
		},
		container: function container () {
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
		getItemFromElement: function getItemFromElement (element) {
			var foundElement;
			
			for (var item in items) {
				if (element.id === items[item].element().id || element.getAttribute('data-item-reference') === items[item].element().id) {
					foundElement = items[item];
				}
			}
			
			return foundElement;
		},
		getItemImage: function getItemImage (image) {
			var itemImagesRoot = document.getElementById('game').getAttribute('data-item-images-root');
			
			if (typeof image !== 'undefined') {
				return itemImagesRoot + '/' + image;
			}
			else {
				return itemImagesRoot + '/../transparent.png';
			}
		},
		selectItem: function selectItem (item) {
			room.deselectAllItems();
			actions.buttons.deactivateAll();
			inventory.deselectAll();
			
			if (item.element().id !== "background") {
				item.element().classList.add('selected');
				panels.action.show();
			}
			else {
				panels.hideAll();
			}
		
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
		},
		deselectItem: function deselectItem (item) {
			item.element().classList.remove('selected');
		},
		deselectAllItems: function deselectAllItems () {
			actions.buttons.deactivateAll();
			
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
					var newItem = document.createElementNS('http://www.w3.org/2000/svg','image'),
						itemImagesRoot = document.getElementById('game').getAttribute('data-item-images-root');
					
					item = items[item];
						
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
			actions.buttons.initialize();
					
			//Add title and description
			var clickEvent = new Event('click');
			room.background().dispatchEvent(clickEvent);
		}
	};	
	
	//Make some stuff happen
	room.load();
}());