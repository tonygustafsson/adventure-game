var game = (function () {
	"use strict";
	
	var actions = {
		takeButton: document.getElementById('action-take'),
		leaveButton: document.getElementById('action-leave'),
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
		reset: function reset () {
			localStorage.clear();
	
			for (var item in items) {
				room.showItem(items[item]);
			};
			
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
		select: function select (item) {
			inventory.deselectAll();
			item.parentNode.classList.add('selected');
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
			var inventorySvg = document.createElementNS("http://www.w3.org/2000/svg", "svg"),
				image = document.createElementNS("http://www.w3.org/2000/svg", "image");
				
			image.setAttribute('width', "200");
			image.setAttribute('height', "200");
			image.setAttribute('x', "0");
			image.setAttribute('y', "0");
			image.setAttributeNS('http://www.w3.org/1999/xlink','xlink:href', item.image);
			image.classList.add('invisible');

			inventorySvg.setAttribute('data-item-reference', item.id);
			inventorySvg.id = 'item-reference-' + item.id;
			inventorySvg.classList.add('inventory-item');
		
			inventorySvg.addEventListener('click', function() {
				//Show take button
				inventory.select(image);
			});
		
			setTimeout(function() {
				//Fade in
				image.classList.remove('invisible');
			}, 250);
			
			inventorySvg.appendChild(image);
			inventory.itemContainer.appendChild(inventorySvg);
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
			return document.getElementById("svg-container");
		},
		image: "svg/room.svg",
		selectedItem: function selectedItem () {
			var element = room.container().querySelector('.selected');
			return room.getItemFromElement(element);
		},
		getItemFromElement: function getItemFromElement(element) {
			var foundElement;
			
			for (var item in items) {
				if (element.id === items[item].element().id || element.getAttribute('data-item-reference') === items[item].element().id) {
					foundElement = items[item];
				}
			};
			
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
			
			room.description.add(item);
		},
		deselectItem: function deselectItem (item) {
			item.element().classList.remove('selected');
		},
		deselectAllItems: function deselectAllItems () {
			actions.deactivateAllButtons();
			
			for (var item in items) {
				items[item].element().classList.remove('selected');
			};
		},
		description: {
			element: function element() {
				return document.getElementById("description");	
			},
			add: function add(item) {
				room.description.reset();
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
			},	
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
				newItem.setAttribute('data-takable', item.takable ? "true" : "false")
				newItem.classList.add('item');
				
				(function(item) {
					newItem.addEventListener('click', function () {
						room.selectItem(item);
					});
				})(item);

				newItem.classList.add('item');
				
				if (localStorage.getItem(item.id) === "invisible") {
					newItem.classList.add('invisible');
					inventory.save(item);
				}

				group.appendChild(newItem);
			}
		},
		load: function load () {
			room.createItems();
					
			//Add title and description
			var clickEvent = new Event('click');
			room.background().dispatchEvent(clickEvent);
		}
	};
	
	var items =  {
		background: {
			id: "background",
			width: 1920,
			height: 1200,
			x: -1072.1428,
			y: -67.637794,
			image: "items/background.jpg",
			title: "Room",
			description: "You are standing in beautiful room with a view of the beach, just a few meeters outside. You are feeling more relaxed already.",
			takable: false,
			element: function () {
				return document.getElementById(this.id);
			}
		},
		flower: {
			id: "flower",
			width: 328,
			height: 468,
			x: 445,
			y: 302,
			image: "items/flower.jpg",
			title: "Flower",
			description: "A white lily are placed in the room and gives of a nice smell.",
			takable: true,
			element: function () {
				return document.getElementById(this.id);
			}
		},
		coffee: {
			id: "coffee",
			width: 216,
			height: 182,
			x: -192,
			y: 837,
			image: "items/coffee.jpg",
			title: "Coffee cup",
			description: "Hm, someone has just left a cup of coffee for you? How nice of them.",
			takable: true,
			element: function () {
				return document.getElementById(this.id);
			}
		},
		sink: {
			id: "sink",
			width: 78,
			height: 41,
			x: 228,
			y: 464,
			image: "items/sink.jpg",
			title: "Sink",
			description: "This green sink seems to have been cleaned recently...",
			takable: true,
			element: function () {
				return document.getElementById(this.id);
			}
		},
		television: {
			id: "television",
			width: 260,
			height: 230,
			x: -900,
			y: 470,
			image: "items/transparent.png",
			title: "Television",
			description: "The television seems to be turned of at the moment.",
			element: function () {
				return document.getElementById(this.id);
			}
		},
		egg: {
			id: "egg",
			width: 87,
			height: 92,
			x: 126,
			y: 864,
			image: "items/transparent.png",
			title: "Egg",
			description: "This brown egg does look mighty fine, but you are not hungry.",
			element: function () {
				return document.getElementById(this.id);
			}
		},
		sky: {
			id: "sky",
			width: 350,
			height: 168,
			x: -517,
			y: 229,
			image: "items/transparent.png",
			title: "Sky",
			description: "The weather seems nice today, how about going for a swim?",
			element: function () {
				return document.getElementById(this.id);
			}
		},
		bed: {
			id: "bed",
			width: 157,
			height: 115,
			x: -164,
			y: 653,
			image: "items/transparent.png",
			title: "Bed",
			description: "Oh, nicely done bed! It's to bright to sleep right now...",
			element: function () {
				return document.getElementById(this.id);
			}
		},
		sunbed: {
			id: "sunbed",
			width: 203,
			height: 112,
			x: -460,
			y: 584,
			image: "items/transparent.png",
			title: "Sumbed",
			description: "Oh my god, that looks comfy enough for you. How about a day just chillin'?",
			element: function () {
				return document.getElementById(this.id);
			}
		},
		buns: {
			id: "buns",
			width: 242,
			height: 163,
			x: -409,
			y: 968,
			image: "items/transparent.png",
			title: "Buns",
			description: "Hm do you think they will notice if I only take one?",
			element: function () {
				return document.getElementById(this.id);
			}
		}
	};
	
	//Make some stuff happen
	room.load();
	actions.initTakeButton();
	actions.initLeaveButton();
	actions.initResetButton();
}());