"use strict";

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
		smellable: true,
		smellMessage: "A wonderful mix of sweet and fruity.",
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
		smellable: true,
		smellMessage: "Oh that scent of Java in the morning!",
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
		takable: false,
		usable: true,
		useMessage: "I don't think you should turn on the TV right now, someone might here you?",
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
		title: "Sunbed",
		description: "Oh my god, that looks comfy enough for you. How about a day just chillin'?",
		takable: false,
		usable: true,
		useMessage: "I don't think they want to use their sunbed...",
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