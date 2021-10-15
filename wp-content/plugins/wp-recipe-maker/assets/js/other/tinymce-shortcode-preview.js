(function() {
	tinymce.PluginManager.add('wprecipemaker', function(editor, url) {
		function replaceShortcodes(content) {
			return content.replace(/\[wprm-recipe ([^\]]*)\]/g, function(match) {
				return html(match);
			});
		}

		function html(data) {
			var id = data.match(/id="?'?(\d+)/i);
			
			if(!id) {
				return data;
			}

			data = window.encodeURIComponent(data);

			var ajax_data = {
				action: 'wprm_shortcode_preview',
				security: wprm_admin.nonce,
				recipe_id: id[1]
			};

			jQuery.post(wprm_admin.ajax_url, ajax_data, function(preview) {
				var content = editor.getContent({format: 'raw'});
				content = content.replace('>Loading WP Recipe Maker #' + id[1] + '<', '>' + preview + '<');
				editor.setContent(content);
			}, 'html');

			return '<span class="wprm-placeholder" contentEditable="false">&nbsp;</span><div class="wprm-shortcode" style="display: block; text-align: left; cursor: pointer; margin: 5px; padding: 10px; border: 1px solid #999;" contentEditable="false" ' +
					'data-wprm-recipe="' + id[1] + '" data-wprm-shortcode="' + data + '" data-mce-resize="false" data-mce-placeholder="1">Loading WP Recipe Maker #' + id[1] + '</div><span class="wprm-placeholder" contentEditable="false">&nbsp;</span>';
		}

		function restoreShortcodes(content) {
			function getAttr(str, name) {
				name = new RegExp(name + '=\"([^\"]+)\"').exec(str);
				return name ? window.decodeURIComponent(name[1]) : '';
			}

			content = content.replace(/<p><span class="wprm-(?=(.*?span>))\1\s*<\/p>/g, '');
			content = content.replace(/<span class="wprm-.*?span>/g, '');

			return content.replace(/(?:<p(?: [^>]+)?>)*(<div [^>]+>[\s\S]*?<\/div>)(?:<\/p>)*/g, function(match, div) {
				var data = getAttr(div, 'data-wprm-shortcode');

				if (data) {
					return '<p>' + data + '</p>';
				}

				return match;
			});
		}

		editor.on('mouseup', function(event) {
			var dom = editor.dom,
				node = event.target,
				shortcode = jQuery(node).hasClass('wprm-shortcode') ? jQuery(node) : jQuery(node).parents('.wprm-shortcode');

			if (event.button !== 2 && shortcode.length > 0) {
				if (dom.getAttrib(node, 'data-wprm-recipe-remove')) {
					if (confirm(wprm_admin.text.shortcode_remove)) {
						editor.dom.remove(node.parentNode);
					}
				} else {
					var id = jQuery(shortcode).data('wprm-recipe');
					WPRM_Modal.open( 'recipe', {
						recipeId: id,
						saveCallback: ( recipe ) => {
							// Refresh tinyMCE.
							if ( typeof tinyMCE !== 'undefined' && tinyMCE.get(editor.id) && !tinyMCE.get(editor.id).isHidden() ) {
								tinyMCE.get(editor.id).focus(true);
								tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent());
							}
						},
					} );
				}
			}
		});

		editor.on('BeforeSetContent', function(event) {
			if(event.content.indexOf('<yoastmark') === -1) {
				event.content = event.content.replace(/(<p>)?\s*<span class="wprm-placeholder" data-mce-contenteditable="false">&nbsp;<\/span>\s*(<\/p>)?/gi,'');
				event.content = event.content.replace(/^(\s*<p>)(\s*\[wprm-recipe)/, '$1<span class="wprm-placeholder" contentEditable="false">&nbsp;</span>$2');
				event.content = replaceShortcodes(event.content);
			}
		});

		editor.on('PostProcess', function(event) {
			if (event.get) {
				event.content = restoreShortcodes(event.content);
			}
		});
	});
})();
