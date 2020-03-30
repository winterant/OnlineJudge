jQeury.textareafullscreen
=============

Jquery plugin textarea fullscreen mode

# Install
Insert this code before tag `</head>`
```
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="/jquery.textareafullscreen/jquery.textareafullscreen.js"></script>
<link rel="stylesheet" href="/jquery.textareafullscreen/textareafullscreen.css">
```
# Use
```
$(document).ready(function() {
	$('#demo').textareafullscreen({
		overlay: true, // Overlay
		maxWidth: '80%', // Max width
		maxHeight: '80%', // Max height
		key: 'f' // default null, crtl + key to toggle fullscreen
	});
});
```
[Demo](http://creoart.github.io/jquery.textareafullscreen)
