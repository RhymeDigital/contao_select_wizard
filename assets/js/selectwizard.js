/**
 * Class BackendSelectWizard
 *
 * Provide methods to handle back end tasks.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 */
var BackendSelectWizard =
{

	/**
	 * Select wizard
	 *
	 * @param {object} el      The DOM element
	 * @param {string} command The command name
	 * @param {string} id      The ID of the target element
	 */
	selectWizard: function(el, command, id) {
		var table = $(id),
			tbody = table.getElement('tbody'),
			parent = $(el).getParent('tr'),
			rows = tbody.getChildren(),
			tabindex = tbody.get('data-tabindex'),
			input, select, childs, a, i, j, k;

		Backend.getScrollOffset();

		switch (command) {
			case 'copy':
				var tr = new Element('tr');
				childs = parent.getChildren();
				for (i=0; i<childs.length; i++) {
					var next = childs[i].clone(true).inject(tr, 'bottom');
					if (select = childs[i].getFirst('select')) {
						next.getFirst('select').value = select.value;
					}
				}
				tr.inject(parent, 'after');
				tr.getElement('.chzn-container').destroy();
				//tr.getElement('.tl_select_column').destroy();
				new Chosen(tr.getElement('select.tl_select'));
				Stylect.convertSelects();
				Backend.convertEnableModules();
				break;
			case 'up':
				if (tr = parent.getPrevious('tr')) {
					parent.inject(tr, 'before');
				} else {
					parent.inject(tbody, 'bottom');
				}
				break;
			case 'down':
				if (tr = parent.getNext('tr')) {
					parent.inject(tr, 'after');
				} else {
					parent.inject(tbody, 'top');
				}
				break;
			case 'delete':
				if (rows.length > 1) {
					parent.destroy();
				}
				break;
		}

		rows = tbody.getChildren();

		for (i=0; i<rows.length; i++) {
			childs = rows[i].getChildren();
			for (j=0; j<childs.length; j++) {
				if (a = childs[j].getFirst('a.chzn-single')) {
					a.set('tabindex', tabindex++);
				}
				if (select = childs[j].getFirst('select')) {
					select.name = select.name.replace(/\[[0-9]+\]/g, '[' + i + ']');
				childs[j].getElement('.chzn-container').destroy();
				//tr.getElement('.tl_select_column').destroy();
				new Chosen(childs[j].getElement('select.tl_select'));
				Stylect.convertSelects();
				Backend.convertEnableModules();
				}
				if (input = childs[j].getFirst('input[type="checkbox"]')) {
					input.set('tabindex', tabindex++);
					input.name = input.name.replace(/\[[0-9]+\]/g, '[' + i + ']');
				}
			}
			
			// Update link cids too
			if (i >= rows.length) continue;
			var links = rows[i].getElements('td a');
			for (k = 0; k < links.length; k++) {
				if (!links[k].href || links[k].href.indexOf('&cid') == -1) continue;
				links[k].href = links[k].href.replace(/cid=[0-9]*/, 'cid=' + i);
			}
		}

		new Sortables(tbody, {
			contstrain: true,
			opacity: 0.6,
			handle: '.drag-handle'
		});
	},
	
	/**
	 * Make the wizards sortable
	 */
	makeWizardsSortable: function() {
		$$('.tl_selectwizard').each(function(el) {
			new Sortables(el.getElement('.sortable'), {
				contstrain: true,
				opacity: 0.6,
				handle: '.drag-handle'
			});
		});
	},
	
	/**
	 * Update the "edit module" links in the module wizard
	 *
	 * @param {object} el The DOM element
	 */
	updateSelectLink: function(el) {
		var td = el.getParent('tr').getLast('td'),
			a = td.getElement('a.select_link');

		if (!a || !a.href) return;

		a.href = a.href.replace(/id=[0-9]+/, 'id=' + el.value);

		if (el.value > 0) {
			td.getElement('a.select_link').setStyle('display', 'inline');
			td.getElement('img.select_image').setStyle('display', 'none');
		} else {
			td.getElement('a.select_link').setStyle('display', 'none');
			td.getElement('img.select_image').setStyle('display', 'inline');
		}
	},

	/**
	 * Convert the "enable select" checkboxes
	 */
	convertEnableSelects: function() {
		$$('img.mw_enable').filter(function(el) {
			return !el.hasEvent('click');
		}).each(function(el) {
			el.addEvent('click', function() {
				Backend.getScrollOffset();
				var cbx = el.getNext('input');
				if (cbx.checked) {
					cbx.checked = '';
					el.src = el.src.replace('visible.gif', 'invisible.gif');
				} else {
					cbx.checked = 'checked';
					el.src = el.src.replace('invisible.gif', 'visible.gif');
				}
			});
		});
	}
};


// Initialize the back end script
window.addEvent('domready', function() {

	BackendSelectWizard.convertEnableSelects();
	BackendSelectWizard.makeWizardsSortable();
});

// Re-apply certain changes upon ajax_change
window.addEvent('ajax_change', function() {
	BackendSelectWizard.makeWizardsSortable();
});

