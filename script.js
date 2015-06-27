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
			var inventoryItem = document.getElementById('item-reference-' + item.element.id);
			
			room.showItem(item);
			inventory.remove(inventoryItem);
			actions.deactivateAllButtons();
		},
		reset: function reset () {
			localStorage.clear();
	
			room.items.forEach(function(item) {
				room.showItem(item);
			});
			
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
			var newSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg"),
				image = item.element.cloneNode(true);
				
			image.setAttribute('x', 0);
			image.setAttribute('y', 0);
			newSvg.setAttribute('data-item-reference', item.element.id);
			newSvg.id = 'item-reference-' + item.element.id;
			newSvg.classList.add('inventory-item');
		
			newSvg.addEventListener('click', function() {
				//Show take button
				inventory.select(image);
			});
		
			setTimeout(function() {
				//Fade in
				image.classList.remove('invisible');
			}, 250);
			
			newSvg.appendChild(image);
			inventory.itemContainer.appendChild(newSvg);
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
		items: [],
		getItems: function getItems () {
			var imageItems = document.querySelectorAll('#svg-container image'),
				itemList = [];
				
			[].forEach.call(imageItems, function(item) {
				var thisItem = {
					"element": item,
					"takable": item.getAttribute('data-action-take') === "true" ? true : false,
					"title": item.getAttribute('data-title'),
					"description": item.getAttribute('data-description')
				};
					
				itemList.push(thisItem);
			});
			
			return itemList;
		},
		selectedItem: function selectedItem () {
			var element = room.container().querySelector('.selected');
			return room.getItemFromElement(element);
		},
		getItemFromElement: function getItemFromElement(element) {
			var foundElement;
			
			room.items.forEach(function (item) {
				if (element.id === item.element.id || element.getAttribute('data-item-reference') === item.element.id) {
					foundElement = item;
				}
			});
			
			return foundElement;
		},
		selectItem: function selectItem (item) {
			actions.deactivateAllButtons();
			inventory.deselectAll();
			
			if (item.element.id !== "background") {
				item.element.classList.add('selected');
			}
		
			if (item.takable) {
				actions.activateButton(actions.takeButton);
			}
			
			room.description.add(item);
		},
		deselectItem: function deselectItem (item) {
			item.element.classList.remove('selected');
		},
		deselectAllItems: function deselectAllItems () {
			actions.deactivateAllButtons();
			
			room.items.forEach(function(item) {
				item.element.classList.remove('selected');
			});
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
			item.element.classList.remove('invisible');
			localStorage.removeItem(item.element.id);
		},
		hideItem: function hideItem (item) {
			room.deselectItem(item);
			item.element.classList.add('invisible');
			localStorage.setItem(item.element.id, "invisible");
		},
		load: function load () {
			var ajax = new XMLHttpRequest();
			
			ajax.open("GET", room.image, true);
			ajax.send();
			ajax.onload = function load () {
				room.container().innerHTML = ajax.responseText;
				room.items = room.getItems();
				
				room.items.forEach(function goThroughItems (item) {
					item.element.classList.add('item');
								
					if (localStorage.getItem(item.element.id) === "invisible") {
						room.hideItem(item);
						inventory.save(item);
					}
					
					item.element.addEventListener('click', function() {
						room.deselectAllItems();
						room.selectItem(item);
					});
				});
						
				//Add title and description
				var clickEvent = new Event('click');
    			room.background().dispatchEvent(clickEvent);
			};
		}
	};
	
	//Make some stuff happen
	room.load();
	actions.initTakeButton();
	actions.initLeaveButton();
	actions.initResetButton();
}());