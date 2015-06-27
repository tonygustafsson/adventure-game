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
				var selectedItem = document.querySelector('.selected');
				actions.take(selectedItem);
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
		leave: function leave (inventoryItem) {
			var itemRef = inventoryItem.getAttribute('data-item-reference'),
				item = document.getElementById(itemRef);
			
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
			return document.querySelector('#inventory-items .selected');
		},
		select: function select (item) {
			inventory.deselectAll();
			item.parentNode.classList.add('selected');
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
				image = item.cloneNode(true);
				
			image.setAttribute('x', 0);
			image.setAttribute('y', 0);
			newSvg.setAttribute('data-item-reference', item.id);
			newSvg.id = 'item-reference-' + item.id;
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
		remove: function remove (item) {
			inventory.deselectAll();
			item.classList.add('invisible');
			
			setTimeout(function() {
				//Fade out
				inventory.itemContainer.removeChild(item);
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
				if (item.id !== "background") {
					itemList.push(item);
				}
			});
			
			return itemList;
		},
		selectItem: function selectItem (item) {
			item.classList.add('selected');
			actions.deactivateAllButtons();
		
			if (item.getAttribute('data-action-take') === "true") {
				actions.activateButton(actions.takeButton);
			}
		},
		deselectItem: function deselectItem (item) {
			item.classList.remove('selected');
		},
		deselectAllItems: function deselectAllItems () {
			actions.deactivateAllButtons();
			
			room.items.forEach(function(item) {
				item.classList.remove('selected');
			});
		},
		showItem: function showItem (item) {
			item.classList.remove('invisible');
			localStorage.removeItem(item.id);
		},
		hideItem: function hideItem (item) {
			room.deselectItem(item);
			item.classList.add('invisible');
			localStorage.setItem(item.id, "invisible");
		},
		load: function load () {
			var ajax = new XMLHttpRequest();
			
			ajax.open("GET", room.image, true);
			ajax.send();
			ajax.onload = function load () {
				room.container().innerHTML = ajax.responseText;
				room.items = room.getItems();
				
				room.items.forEach(function goThroughItems (item) {
					item.classList.add('item');
					
					room.background().addEventListener('click', function() {
						room.deselectAllItems();
					});
					
					if (localStorage.getItem(item.id) === "invisible") {
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
	
	//Make some stuff happen
	room.load();
	actions.initTakeButton();
	actions.initLeaveButton();
	actions.initResetButton();
}());