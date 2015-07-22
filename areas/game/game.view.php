<div id="settings-panel" class="settings-panel">
	<h2>Settings</h2>
	
	<p>Reset the game with the button below.</p>
	<p>
		<button id="action-reset" class="action-button action-button-visible persistant-button">Reset</button>
		<button class="action-button action-cancel-button action-button-visible persistant-button">Cancel</button>
	</p>
</div>

<aside id="map-panel" class="map-panel">
	<h2>Map</h2>
	<img src="<?=$this->opus->url('assets/images/map.jpg')?>" class="full-width">
	<a href="#" id="expand-map-panel" class="expand-map-panel">
		Map
	</a>
</aside>

<div id="game" class="game" data-item-images-root="<?=$room_images_root?>">	
	<svg
	   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	   xmlns:svg="http://www.w3.org/2000/svg"
	   xmlns="http://www.w3.org/2000/svg"
	   xmlns:xlink="http://www.w3.org/1999/xlink"
	   version="1.1"
	   id="game-svg"
	   viewBox="0 0 1920 1200"
	   height="1200"
	   width="1920">
	  		<g id="svg-layer"></g>
	</svg>
	
	<div id="action-panel" class="action-panel">
		<div id="description"></div>
		
		<div id="message"></div>
		
		<div id="actions">
			<button class="action-button" id="action-take">Take</button>
			<button class="action-button" id="action-leave">Leave</button>
			<button class="action-button" id="action-use">Use</button>
			<button class="action-button" id="action-smell">Smell</button>
			<button class="action-button action-cancel-button action-button-visible persistant-button">Cancel</button>
		</div>
		
		<a href="#" id="expand-action-panel" class="expand-action-panel">
			<?=$room_data->title?>
			<a id="expand-settings-panel" class="expand-settings-panel"><img src="<?=$this->opus->url('assets/images/settings.png')?>" alt="Settings"></a>
		</a>
	</div>
</div>

<aside id="inventory-panel" class="inventory-panel">
	<h2>Inventory</h2>
	<div id="inventory-items"></div>
	<a href="#" id="expand-inventory-panel" class="expand-inventory-panel">Inventory</a>
</aside>