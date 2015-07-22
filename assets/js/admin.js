function initRemoveItemButtons () {
	var removeItemButtons = document.querySelectorAll('.remove-item-button');
	
	var removeItem = function removeItem (e) {
		var itemCounter = e.target.getAttribute('data-item-remove'),
			fieldset = document.getElementById('item_id_' + itemCounter);
		
		fieldset.parentNode.removeChild(e.target.parentNode);
	}
	
	for (var i = 0; i < removeItemButtons.length; i++) {
		var removeButton = removeItemButtons[i];
		removeButton.addEventListener('click', removeItem);
	}
}

function initNewItemButtons () {
	var newItemButton = document.getElementById('new-item-button');
	
	if (newItemButton !== null) {
		newItemButton.addEventListener('click', function () {
			var newItemTemplate = document.getElementById('new-item-template'),
				newElement = document.createElement("fieldset"),
				itemsArea = document.getElementById("items"),
				allFieldsets = itemsArea.getElementsByTagName("fieldset"),
				lastItemFieldset = allFieldsets[itemsArea.getElementsByTagName("fieldset").length - 1],
				nextItemCounter = typeof lastItemFieldset !== "undefined" ? parseInt(lastItemFieldset.getAttribute('data-item-counter'), 10) + 1 : 1,
				newHtml = newItemTemplate.innerHTML.replace(/###/g, nextItemCounter.toString());
			
			newElement.id = "item_id_" + nextItemCounter.toString();
			newElement.setAttribute('data-item-counter', nextItemCounter.toString());
			newElement.innerHTML = newHtml;
			itemsArea.appendChild(newElement);
			
			initRemoveItemButtons();
		});
	}
}

initRemoveItemButtons();
initNewItemButtons();