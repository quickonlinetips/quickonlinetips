/*
 * Quick Code Styler
 * Adds syntax colours and a Copy button to code blocks.
 * From QuickOnlineTips.com - Tech help that works. Since 2004.
 *
 * No outside library. The plugin only loads this file on pages that
 * show code, and only when at least one of the two switches on the
 * settings page is on. The switches arrive in window.quickCodeStyler.
 */
( function () {
	'use strict';

	var settings = window.quickCodeStyler || {};
	var doc = document;

	/*
	 * One pattern finds every kind of token. Each bracket group is one kind,
	 * in this order (the letters match the CSS classes qcs-c, qcs-s ...):
	 *   c  comments: <!-- -->, /* * /, // and "# " comments (a # followed by
	 *      a space, so CSS ids like #main and colours like #fff stay as code)
	 *   s  quoted text: "..." and '...'
	 *   t  HTML tags: <div, </div, />
	 *   a  attributes: the name before an = sign, like href=
	 *   k  keywords common to JavaScript, PHP, Python and shell: function, def, return, if ...
	 *   n  numbers
	 */
	var TOKENS = /(<!--[\s\S]*?-->|\/\*[\s\S]*?\*\/|(?:^|[ \t])\/\/[^\n]*|(?:^|[ \t])#(?=[ \t!]|$)[^\n]*)|("(?:[^"\\\n]|\\.)*"|'(?:[^'\\\n]|\\.)*')|(<\/?[A-Za-z][\w:.-]*|\/?>)|([A-Za-z_:][\w:.-]*(?==))|\b(var|let|const|function|def|return|if|else|elif|elseif|foreach|for|while|do|done|then|fi|in|new|true|false|True|False|null|None|async|await|typeof|this|self|import|from|export|echo|sudo|class|public|private|static|try|catch|except|finally|throw|raise|lambda|and|or|not|important)\b|\b(\d+(?:\.\d+)?)\b/gm;
	var KINDS = 'cstakn';
	var MAX_LENGTH = 20000; // Very long blocks are left plain, to keep pages fast.

	function escapeHtml( text ) {
		return text.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
	}

	/*
	 * Is the markup inside this code only leftover <span> tags, the kind that
	 * come along when code is copied from another web page? Those are safe to
	 * strip and colour again. Anything else is left alone: real highlighter
	 * plugins (Prism's "token", highlight.js's "hljs-", Shiki's inline styles)
	 * and tags an author added on purpose, like <strong>, <mark> or <a>.
	 */
	function onlyLeftoverSpans( code ) {
		var elements = code.querySelectorAll( '*' );

		for ( var i = 0; i < elements.length; i++ ) {
			var element = elements[ i ];
			if (
				element.tagName !== 'SPAN' ||
				element.hasAttribute( 'style' ) ||
				/(^|\s)(token|hljs)/.test( element.className )
			) {
				return false;
			}
		}

		return true;
	}

	// Wrap each token in a <span> with its colour class.
	function colourBlock( pre ) {
		var code = pre.querySelector( 'code' ) || pre;

		// Markup already inside: colour again only if it is leftover spans.
		if ( code.children.length && ! onlyLeftoverSpans( code ) ) {
			return;
		}

		var text = code.textContent;
		if ( text.length > MAX_LENGTH ) {
			return;
		}

		var html = '';
		var last = 0;

		text.replace( TOKENS, function ( match ) {
			var args = arguments;
			var offset = args[ 7 ];
			var group = 1;

			// Find which bracket group matched.
			while ( group < 7 && args[ group ] === undefined ) {
				group++;
			}

			html += escapeHtml( text.slice( last, offset ) ) +
				'<span class="qcs-' + KINDS.charAt( group - 1 ) + '">' + escapeHtml( match ) + '</span>';
			last = offset + match.length;
			return match;
		} );

		code.innerHTML = html + escapeHtml( text.slice( last ) );
	}

	// Older browsers without the clipboard API: copy through a hidden text box.
	function copyTheOldWay( text ) {
		var box = doc.createElement( 'textarea' );
		var copied = false;

		box.value = text;
		box.setAttribute( 'readonly', '' );
		box.style.position = 'fixed';
		box.style.top = '-999px';
		doc.body.appendChild( box );
		box.select();

		try {
			copied = doc.execCommand( 'copy' );
		} catch ( e ) {}

		box.remove();
		return copied;
	}

	function addCopyButton( pre ) {
		var button = doc.createElement( 'button' );
		var label = settings.copyText || 'Copy';
		var doneLabel = settings.copiedText || 'Copied!';

		button.type = 'button';
		button.className = 'qcs-copy';
		button.textContent = label;
		button.setAttribute( 'aria-label', settings.copyLabel || 'Copy code' );

		function showDone() {
			button.textContent = doneLabel;
			button.classList.add( 'is-done' );
			setTimeout( function () {
				button.textContent = label;
				button.classList.remove( 'is-done' );
			}, 1600 );
		}

		button.addEventListener( 'click', function () {
			// Copy the code only, never the button's own text.
			var copy = pre.cloneNode( true );
			var extra = copy.querySelector( '.qcs-copy' );
			if ( extra ) {
				extra.remove();
			}
			var text = copy.textContent;
			var clipboard = window.navigator.clipboard;

			if ( clipboard && clipboard.writeText ) {
				clipboard.writeText( text ).then( showDone, function () {
					if ( copyTheOldWay( text ) ) {
						showDone();
					}
				} );
			} else if ( copyTheOldWay( text ) ) {
				showDone();
			}
		} );

		pre.appendChild( button );
	}

	function run() {
		doc.querySelectorAll( '.qcs-on pre' ).forEach( function ( pre ) {
			if ( pre.hasAttribute( 'data-qcs' ) ) {
				return; // Already done.
			}
			pre.setAttribute( 'data-qcs', '' );

			if ( settings.color ) {
				try {
					colourBlock( pre );
				} catch ( e ) {}
			}

			if ( settings.copy ) {
				addCopyButton( pre );
			}
		} );
	}

	if ( doc.readyState === 'loading' ) {
		doc.addEventListener( 'DOMContentLoaded', run );
	} else {
		run();
	}
}() );
