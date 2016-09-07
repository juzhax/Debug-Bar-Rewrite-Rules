/* Debug Rewrite Rules Panel / WordPress Admin Page fucntionality. */
(function($) {
	$(function() {
		var debugBarRewriteRulesApp = {
			/* 	Initial Settings. */
			settings: {

			},

			/**
			 *  Ajax Based Rewrite Rules Flush.
			 */
			ajax: function() {

				// We have only one link in headings seaction.
				$('.debug-bar-rewrites-urls a').bind('click', function(e) {

					e.preventDefault();

					$(this).find('.spinner').css({
						visibility: 'visible'
					});
					var $_el = this;

					$.post($this.settings.ajaxurl, {
						action: 'debug_bar_rewrite_rules',
						nonce: $this.settings.nonce
					}, function(data) {

						$($_el).find('.spinner').css({
							visibility: 'hidden'
						});

						// Replacing Tables
						$.each(['rules', 'filters'], function(index, value) {
							if (typeof data[value] != "underfined" && $('.dbrr.' + value).length > 0) {
								$('.dbrr.' + value).html($('table', $(data[value])).parent().html());
							}
						});

						// Replacing Counts.
						$.each(['rules', 'filters_hooked', 'filters'], function(index, value) {
							if (typeof data['count_' + value] != "underfined" && $('h2.dbrr_count_' + value + ' i').length > 0) {
								$('h2.dbrr_count_' + value + ' i').text(data['count_' + value]);
							}
						});

					}, 'json'); // End of AJAX call.
				});
			},

			/**
			 *  Search for url match in patterns.
			 */
			test: function() {

				// Clickcing wp url and making webpath active.
				$('.mono.domain')
					.bind('click', function() {
						$('.mono.search').focus();
					}).trigger('click');

				$('.mono.search')
					.bind('focus', function() {
						$(this).parent().addClass('active');

						if (!$('.dbrr.rules table thead tr th.col-match').length) {

							$('<th/>')
								.addClass('col-match')
								.css('width', '30%')
								.text($this.settings.matches)
								.appendTo($('.dbrr.rules table thead tr'))
								.parent()
								.find('th.col-data')
								.css('width', '35%');

							$('<td/>')
								.addClass('col-match')
								.html(' ')
								.appendTo($('.dbrr.rules table tbody tr'));
						}
					})

				.bind('blur', function() {
					if ($.trim($(this).val()) == '' && $('.col-match').length) {
						$(this).parent().removeClass('active');
						$('.col-match').remove();
					}
				})

				.bind('keyup', $this._filter_match)
					.bind('keyup', $this._filter_sarched)

				.bind('focus', $this._filter_match)
					.bind('focus', $this._filter_sarched);

			},

			calc_domain_width: function() {

				if ($('div.filterui input.mono.domain').is(":visible")) {
					$('.filterui .url').append('<span class="mono domain">' + $('div.filterui .mono.domain').val() + '</span>');
					$('div.filterui input.mono.domain').css('width', $('div.filterui span.mono.domain').width() + 3);
					$('div.filterui span.mono.domain').remove();
					clearInterval($this.calc_domain_width_timeout);
					$this.calc_domain_width_timeout = 0;
				}

			},

			/**
			 * Search for matches in rewrites.
			 */
			search: function() {

				// What the width of the domain block?
				$this.calc_domain_width_timeout = window.setInterval($this.calc_domain_width, 1000);
				$this.calc_domain_width();

				$('.mono.matches')
					.bind('focus', function() {
						$('div.filterui .url.active').removeClass('active');
						$('.dbrr.rules table 		tr   .col-match').remove();
						$('.dbrr.rules table thead tr th.col-data').css({
							width: '50%'
						});
					})
					.bind('keyup', $this._search_for_match);

			},

			/**
			 *  This function is to cleanup url from the input field if it copy/pasted
			 *  @todo Implement better handling for keyups and keydowns.
			 */
			_search_for_match: function(e) {

				// get keycode of current keypress event
				var code = (e.keyCode || e.which);

				// do nothing if it's an arrow key
				// Ignoting all arrow keys.
				if ([32, 37, 38, 39, 40].indexOf(code) > -1) {
					return;
				}

				if (13 == code) {
					e.preventDefault();
				}

				// search value....
				$this.match = $.trim($(this).val());

				$this._table_reset($('.dbrr.rules table'));

				if ('' != $this.match) {

					$('.dbrr.rules table tbody tr').each(function() {

						if ($(this).text().indexOf($this.match) == -1) {
							$(this).addClass('hidden');
						} else {
							$('td', this).each(function() {
								$(this).html($(this).text().replace($this.match, '<span>' + $this.match + '</span>'));
							});
						}
					});

				}

				//	$this._table_zebra( $('.dbrr.rules table') );

			},

			/**
			 *  This function is to cleanup url from the input field if it copy/pasted
			 *  @todo IMplement better handling for keyups and keydowns.
			 */
			_filter_match: function(e) {
				$el = $(this);

				if (typeof $this.base == 'undefined') {
					$this.base = $this.parse_url($this.settings.home);
				}

				// get keycode of current keypress event
				var code = (e.keyCode || e.which);

				// Ignoting all arrow keys.
				if ([32, 37, 38, 39, 40, 91].indexOf(code) > -1) {
					return;
				}

				// Activate only on Enter
				if (13 == code && $.trim($el.val()) != "") {
					e.preventDefault();
				}

				var current = $this.parse_url($(this).val());

				jQuery.each(['scheme', '://', 'host', 'port', 'path'], function(index, value) {
					val = o = $el.val().split('?')[0].split('#')[0];

					if (typeof current[value] == 'string' && current[value] === $this.base[value] && val.indexOf(current[value]) != -1) {
						val = val.substring(val.indexOf(current[value]) + current[value].length);
					} else if (value == '://' && val.indexOf(value) != -1) {
						val = val.substring(val.indexOf(value) + value.length);
					} else if (value == 'path' && val.indexOf($this.base[value]) == 0) {
						val = val.substring(val.indexOf($this.base[value]) + $this.base[value].length);
					}

					// Additional Starting Trail
					if (value == 'path' && val.substring(0, 1) == '/') {
						val = val.substring(1, val.length);
					}

					$el.val(o != jQuery.trim(val) ? jQuery.trim(val) : o);
				});
			},

			/**
			 *  Testing our search pattern for match.
			 */
			_filter_sarched: function(e) {
				$el = $(this);



				// get keycode of current keypress event
				var code = (e.keyCode || e.which);

				// Ignoting all arrow keys.
				if ([32, 37, 38, 39, 40, 91].indexOf(code) > -1) {
					return;
				}

				// Activate only on Enter
				if (13 == code && $.trim($el.val()) != "") {
					e.preventDefault();
				}

				$this._table_reset($('.dbrr.rules table'));

				// Reset table - removing `span` and cleaning matches table.
				$('.dbrr.rules table tbody tr').each(function() {
					$('td:eq(0), td:eq(1)', $(this)).each(function() {
						jQuery(this).html($(this).text());
					});
					$('td:eq(2)', $(this)).html('');
				});

				/* Now running actual search and replacements. */
				if ($.trim($el.val()) != "") {

					// Creating Rules.
					if (typeof $this.rules == 'undefined') {
						$this.rules = {};
						$('.dbrr.rules table tbody tr').each(function() {
							$this.rules[$(this).attr('id')] = {
								rule: $('td:eq(0)', $(this)).text(),
								match: $('td:eq(1)', $(this)).text()
							};
						});
					}

					jQuery.ajax({
							// async 		: false,
							url: $this.settings.validator,
							crossDomain: true,
							dataType: 'json',
							type: 'POST',
							data: {
								rules: $this.rules,
								search: $el.val()
							}
						})
						.done(function(data) {

							$('#debug').html(data);

							$this._table_reset($('.dbrr.rules table'));


							// Special Case if TWO interfaces open.

							$('.dbrr.rules').each(function() {
								$('table tbody tr', this).each(function(i) {
									if (data.rules[(i + 1)].result == false) {
										jQuery(this).addClass('hidden');
									} else {
										$this.currentRow = this;
										if (typeof data.rules[(i + 1)].vars != "undefined") {
											$.each(data.rules[(i + 1)].vars, function(index, value) {
												jQuery('td:eq(2)', $this.currentRow)
													.append('<div><strong>' + index + '</strong> : ' + value + '</div>');
											});
										}
									}
								});
							});

							$this._table_zebra($('.dbrr.rules table'));
						});

				}



			},

			/**
			 *  Reseting Table rows to make them visible again.
			 */
			_table_reset: function(jQueryTableElement) {

				$('tbody tr', jQueryTableElement)
					.removeClass('alt hidden')
					.each(function() {
						$('td', $(this))
							.each(function() {
								$(this).html($(this).text());
							});
					});
				return $this._table_zebra(jQueryTableElement);
			},

			/**
			 * Painting Table as Zebra.
			 */

			_table_zebra: function(jQueryTableElement) {

				$('tbody tr', jQueryTableElement)
					.filter(':visible')
					.each(function(i) {
						$(this).addClass(((i + 2) % 2 == 0) ? 'alt' : '_');
					});

				return $this;;
			},



			/* php parse_url clone */
			parse_url: function(url, t) {
				/*	Dom parsing of A	*/
				var parser = document.createElement('a');

				if (url.substring(0, 1) != '/' && url.substring(0, 4) != 'http') {
					url = '/' + url;
				}
				//
				// console.log( 'link - ' + url.substring(0, 1) );

				parser.href = url;

				/*	Exception for user:password	*/
				var re = /(\/\/)(.+?)((:(.+?))?)@/i;
				if (re.exec(url)) {
					var result = re.exec(url)
					if (typeof result[2] == 'string' && result[2].length > 0) {
						parser.user = result[2];
					}

					if (typeof result[5] == 'string' && result[5].length > 0) {
						parser.pass = result[5];
					}
				}

				var urls = new Object();
				var urls_php = ['scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'];
				var urls_js = ['protocol', 'hostname', 'port', 'user', 'pass', 'pathname', 'search', 'hash'];

				urls_js.forEach(function(value, index) {
					if (typeof parser[value] == "string" && parser[value].length > 0) {
						switch (value) {
							case "protocol":
								urls[urls_php[index]] = parser[value].replace(":", "");
								break;
							case "hash":
								urls[urls_php[index]] = parser[value].replace("#", "");
								break;
							case "pathname":
								if (parser[value] != "/")
									urls[urls_php[index]] = parser[value];
								break;
							default:
								urls[urls_php[index]] = parser[value];
								break;
						}
					}
				});
				return urls;
			},

			intialize: function(settings) {
				$this = this;

				$this.settings = settings;


				return $this.ajax(),
					$this.search(),
					$this.test();
			}
		};
		debugBarRewriteRulesApp.intialize(debugBarRewriteRules);
	});
})(jQuery);