import '../../css/admin/tools.scss';

let action = false;
let args = {};
let posts = [];
let posts_total = 0;
let reportData = false;

async function postJSON(data) {
	const body = new URLSearchParams(data).toString();
	const response = await fetch(wprm_admin.ajax_url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
		},
		body,
	});

	return response.json();
}

async function postFormData(formData) {
	const response = await fetch(wprm_admin.ajax_url, {
		method: 'POST',
		body: formData,
	});

	return response.json();
}

async function handle_posts() {
	const data = {
		action: 'wprm_' + action,
		security: wprm_admin.nonce,
		posts: JSON.stringify(posts),
		args: args,
	};

	try {
		const out = await postJSON(data);

		if (out.success) {
			posts = out.data.posts_left;
			if (out.data && out.data.report) {
				mergeReportData(out.data.report);
			}
			update_progress_bar();

			if (posts.length > 0) {
				await handle_posts();
			} else {
				render_report();
				const finished = document.querySelector('#wprm-tools-finished');
				if (finished) {
					finished.style.display = 'block';
				}
			}
		} else {
			window.location = out.data.redirect;
		}
	} catch (error) {
		// eslint-disable-next-line no-console
		console.error('WPRM tools request failed', error);
	}
}

function getContrastClass(color) {
	const ctx = document.createElement('canvas').getContext('2d');
	ctx.fillStyle = color;
	const hex = ctx.fillStyle;
	const r = parseInt(hex.slice(1, 3), 16);
	const g = parseInt(hex.slice(3, 5), 16);
	const b = parseInt(hex.slice(5, 7), 16);
	const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
	return luminance > 0.5 ? 'wprm-progress-percentage-dark' : 'wprm-progress-percentage-light';
}

function update_progress_bar() {
	const percentage = (1.0 - posts.length / posts_total) * 100;
	const isComplete = percentage >= 100;
	const bar = document.querySelector('#wprm-tools-progress-bar');
	if (bar) {
		bar.style.width = `${percentage}%`;
		bar.classList.toggle('wprm-progress-complete', isComplete);
	}

	const label = document.querySelector('#wprm-tools-progress-container .wprm-progress-percentage');
	if (label) {
		label.textContent = isComplete ? '100%' : `${percentage.toFixed(1)}%`;
		label.classList.remove('wprm-progress-percentage-light', 'wprm-progress-percentage-dark', 'wprm-progress-percentage-complete');

		if (isComplete) {
			label.classList.add('wprm-progress-percentage-complete');
		} else if (percentage >= 50) {
			const color = getComputedStyle(document.documentElement).getPropertyValue('--wp-admin-theme-color').trim() || '#3858e9';
			label.classList.add(getContrastClass(color));
		}
	}
}

function getOrCreateReportContainer() {
	let reportContainer = document.querySelector('#wprm-tools-report');
	if (reportContainer) {
		return reportContainer;
	}

	const wrap = document.querySelector('.wrap.wprm-tools');
	if (!wrap) {
		return null;
	}

	reportContainer = document.createElement('div');
	reportContainer.id = 'wprm-tools-report';

	const finished = document.querySelector('#wprm-tools-finished');
	if (finished && finished.parentNode) {
		finished.parentNode.insertBefore(reportContainer, finished.nextSibling);
	} else {
		wrap.appendChild(reportContainer);
	}

	return reportContainer;
}

function normalizeReport(report) {
	if (Array.isArray(report)) {
		return {
			toggle: 'Detailed Report',
			title: '',
			columns: [],
			entries: report,
			json_label: 'Full Report JSON',
		};
	}

	if (!report || 'object' !== typeof report) {
		return false;
	}

	return {
		toggle: report.toggle || 'Detailed Report',
		title: report.title || '',
		summary: report.summary || '',
		columns: Array.isArray(report.columns) ? report.columns : [],
		entries: Array.isArray(report.entries) ? report.entries : [],
		json_label: report.json_label || 'Full Report JSON',
	};
}

function mergeReportData(report) {
	const normalized = normalizeReport(report);
	if (!normalized) {
		return;
	}

	if (!reportData) {
		reportData = normalized;
		return;
	}

	if (normalized.title) {
		reportData.title = normalized.title;
	}

	if (normalized.summary) {
		reportData.summary = normalized.summary;
	}

	if (normalized.toggle) {
		reportData.toggle = normalized.toggle;
	}

	if (normalized.json_label) {
		reportData.json_label = normalized.json_label;
	}

	if (normalized.columns.length) {
		reportData.columns = normalized.columns;
	}

	reportData.entries = reportData.entries.concat(normalized.entries);
}

function stringifyReportValue(value) {
	if (Array.isArray(value)) {
		if (!value.length) {
			return '-';
		}

		const allSimpleValues = value.every((item) => 'object' !== typeof item || null === item);
		return allSimpleValues ? value.join(', ') : JSON.stringify(value);
	}

	if ('object' === typeof value && null !== value) {
		return JSON.stringify(value);
	}

	if (false === value || null === value || 'undefined' === typeof value || '' === value) {
		return '-';
	}

	return `${value}`;
}

function getReportColumns(entries, configuredColumns) {
	if (Array.isArray(configuredColumns) && configuredColumns.length) {
		return configuredColumns.map((column) => {
			if ('string' === typeof column) {
				return {
					key: column,
					label: column,
				};
			}

			return column;
		});
	}

	if (!Array.isArray(entries) || !entries.length || 'object' !== typeof entries[0]) {
		return [];
	}

	return Object.keys(entries[0]).map((key) => ({
		key,
		label: key,
	}));
}

function renderReportTable(reportContainer, entries, columns) {
	if (!entries.length || !columns.length) {
		return;
	}

	const table = document.createElement('table');
	table.className = 'widefat striped';

	const thead = document.createElement('thead');
	const headRow = document.createElement('tr');
	columns.forEach((column) => {
		const th = document.createElement('th');
		th.textContent = column.label || column.key;
		headRow.appendChild(th);
	});
	thead.appendChild(headRow);
	table.appendChild(thead);

	const tbody = document.createElement('tbody');
	entries.forEach((entry) => {
		const row = document.createElement('tr');

		columns.forEach((column) => {
			const td = document.createElement('td');
			td.textContent = stringifyReportValue(entry[column.key]);
			row.appendChild(td);
		});

		tbody.appendChild(row);
	});

	table.appendChild(tbody);
	reportContainer.appendChild(table);
}

function render_report() {
	if (!reportData || !Array.isArray(reportData.entries) || !reportData.entries.length) {
		return;
	}

	const reportContainer = getOrCreateReportContainer();
	if (!reportContainer) {
		return;
	}

	reportContainer.textContent = '';

	const details = document.createElement('details');

	const toggle = document.createElement('summary');
	toggle.textContent = reportData.toggle || 'Detailed Report';
	details.appendChild(toggle);

	if (reportData.title) {
		const heading = document.createElement('h3');
		heading.textContent = reportData.title;
		details.appendChild(heading);
	}

	if (reportData.summary) {
		const summary = document.createElement('p');
		summary.textContent = reportData.summary;
		details.appendChild(summary);
	}

	renderReportTable(details, reportData.entries, getReportColumns(reportData.entries, reportData.columns));

	const jsonDetails = document.createElement('details');
	const jsonSummary = document.createElement('summary');
	jsonSummary.textContent = reportData.json_label || 'Full Report JSON';
	jsonDetails.appendChild(jsonSummary);

	const pre = document.createElement('pre');
	pre.textContent = JSON.stringify(reportData, null, 2);
	jsonDetails.appendChild(pre);
	details.appendChild(jsonDetails);

	reportContainer.appendChild(details);
}

function getFilenameFromHeader(header) {
	if (!header) {
		return null;
	}

	const match = header.match(/filename="?([^"]+)"?/i);
	return match && match[1] ? match[1] : null;
}

document.addEventListener('DOMContentLoaded', () => {
	// Import Process
	if (typeof window.wprm_tools !== 'undefined') {
		action = wprm_tools.action;
		args = wprm_tools.args;
		posts = wprm_tools.posts;
		posts_total = wprm_tools.posts.length;
		reportData = false;
		handle_posts();
	}

	// Reset settings
	const resetButton = document.querySelector('#tools_reset_settings');
	if (resetButton) {
		resetButton.addEventListener('click', async (event) => {
			event.preventDefault();

			if (confirm('Are you sure you want to reset all settings?')) {
				const data = {
					action: 'wprm_reset_settings',
					security: wprm_admin.nonce,
				};

				try {
					const out = await postJSON(data);
					if (out.success) {
						window.location = out.data.redirect;
					} else {
						alert('Something went wrong.');
					}
				} catch (error) {
					alert('Something went wrong.');
					// eslint-disable-next-line no-console
					console.error('WPRM tools reset failed', error);
				}
			}
		});
	}

	// Export settings
	const exportButton = document.querySelector('#tools_export_settings');
	if (exportButton) {
		exportButton.addEventListener('click', async (event) => {
			event.preventDefault();
			exportButton.disabled = true;

			const formData = new FormData();
			formData.append('action', 'wprm_export_settings');
			formData.append('security', wprm_admin.nonce);

			try {
				const response = await fetch(wprm_admin.ajax_url, {
					method: 'POST',
					body: formData,
				});

				if (!response.ok) {
					throw new Error('Export request failed');
				}

				const blob = await response.blob();
				const url = window.URL.createObjectURL(blob);
				const filename =
					getFilenameFromHeader(response.headers.get('Content-Disposition')) ||
					`wprm-settings-export-${new Date().toISOString().slice(0, 19).replace(/[T:]/g, '-')}.json`;

				const link = document.createElement('a');
				link.href = url;
				link.download = filename;
				document.body.appendChild(link);
				link.click();
				link.remove();
				window.URL.revokeObjectURL(url);
			} catch (error) {
				alert('Could not export settings. Please try again.');
				// eslint-disable-next-line no-console
				console.error('WPRM settings export failed', error);
			} finally {
				exportButton.disabled = false;
			}
		});
	}

	// Export templates
	const exportTemplatesButton = document.querySelector('#tools_export_templates');
	if (exportTemplatesButton) {
		exportTemplatesButton.addEventListener('click', async (event) => {
			event.preventDefault();
			exportTemplatesButton.disabled = true;

			const formData = new FormData();
			formData.append('action', 'wprm_export_templates');
			formData.append('security', wprm_admin.nonce);

			try {
				const response = await fetch(wprm_admin.ajax_url, {
					method: 'POST',
					body: formData,
				});

				if (!response.ok) {
					throw new Error('Export request failed');
				}

				const blob = await response.blob();
				const url = window.URL.createObjectURL(blob);
				const filename =
					getFilenameFromHeader(response.headers.get('Content-Disposition')) ||
					`wprm-templates-export-${new Date().toISOString().slice(0, 19).replace(/[T:]/g, '-')}.json`;

				const link = document.createElement('a');
				link.href = url;
				link.download = filename;
				document.body.appendChild(link);
				link.click();
				link.remove();
				window.URL.revokeObjectURL(url);
			} catch (error) {
				alert('Could not export templates. Please try again.');
				// eslint-disable-next-line no-console
				console.error('WPRM templates export failed', error);
			} finally {
				exportTemplatesButton.disabled = false;
			}
		});
	}

	// Import settings
	const importForm = document.querySelector('#wprm-import-settings-form');
	if (importForm) {
		const importResult = document.querySelector('#wprm-import-settings-result');
		importForm.addEventListener('submit', async (event) => {
			event.preventDefault();

			const fileInput = importForm.querySelector('input[name="wprm_settings_file"]');
			const submitButton = importForm.querySelector('button');

			if (!fileInput || fileInput.files.length === 0) {
				alert('Please select a JSON file to import.');
				return;
			}

			if (submitButton) {
				submitButton.disabled = true;
			}

			const formData = new FormData();
			formData.append('action', 'wprm_import_settings');
			formData.append('security', wprm_admin.nonce);
			formData.append('wprm_settings_file', fileInput.files[0]);

			try {
				const out = await postFormData(formData);

				const message = out?.data?.message || (out.success ? 'Settings imported successfully.' : 'Import failed.');
				const warnings = Array.isArray(out?.data?.warnings) ? out.data.warnings : [];
				const messageClass = out.success ? 'success' : 'error';

				if (importResult) {
					importResult.textContent = '';
					importResult.classList.remove('error', 'success');
					importResult.classList.add(messageClass);

					const messageSpan = document.createElement('span');
					messageSpan.textContent = message;
					importResult.appendChild(messageSpan);

					if (warnings.length) {
						const warningList = document.createElement('ul');
						warningList.className = 'wprm-import-warning-list';

						warnings.forEach((warning) => {
							const listItem = document.createElement('li');
							listItem.textContent = warning;
							warningList.appendChild(listItem);
						});

						importResult.appendChild(warningList);
					}
				}

				if (out.success) {
					fileInput.value = '';
				}
			} catch (error) {
				if (importResult) {
					importResult.textContent = 'Import failed. Please try again.';
					importResult.classList.remove('success');
					importResult.classList.add('error');
				}
				// eslint-disable-next-line no-console
				console.error('WPRM settings import failed', error);
			} finally {
				if (submitButton) {
					submitButton.disabled = false;
				}
			}
		});
	}

	// Download debug information
	const downloadDebugButton = document.querySelector('#tools_download_debug_info');
	if (downloadDebugButton) {
		downloadDebugButton.addEventListener('click', async (event) => {
			event.preventDefault();
			downloadDebugButton.disabled = true;

			const formData = new FormData();
			formData.append('action', 'wprm_download_debug_info');
			formData.append('security', wprm_admin.nonce);

			try {
				const response = await fetch(wprm_admin.ajax_url, {
					method: 'POST',
					body: formData,
				});

				if (!response.ok) {
					throw new Error('Debug info request failed');
				}

				const blob = await response.blob();
				const url = window.URL.createObjectURL(blob);
				const filename =
					getFilenameFromHeader(response.headers.get('Content-Disposition')) ||
					`wprm-debug-${new Date().toISOString().slice(0, 10)}.json`;

				const link = document.createElement('a');
				link.href = url;
				link.download = filename;
				document.body.appendChild(link);
				link.click();
				link.remove();
				window.URL.revokeObjectURL(url);
			} catch (error) {
				alert('Could not download debug information. Please try again.');
				// eslint-disable-next-line no-console
				console.error('WPRM debug info download failed', error);
			} finally {
				downloadDebugButton.disabled = false;
			}
		});
	}

	// Import templates
	const importTemplatesForm = document.querySelector('#wprm-import-templates-form');
	if (importTemplatesForm) {
		const importTemplatesResult = document.querySelector('#wprm-import-templates-result');
		importTemplatesForm.addEventListener('submit', async (event) => {
			event.preventDefault();

			const fileInput = importTemplatesForm.querySelector('input[name="wprm_templates_file"]');
			const submitButton = importTemplatesForm.querySelector('button');

			if (!fileInput || fileInput.files.length === 0) {
				alert('Please select a JSON file to import.');
				return;
			}

			if (submitButton) {
				submitButton.disabled = true;
			}

			const formData = new FormData();
			formData.append('action', 'wprm_import_templates');
			formData.append('security', wprm_admin.nonce);
			formData.append('wprm_templates_file', fileInput.files[0]);

			try {
				const out = await postFormData(formData);

				const message = out?.data?.message || (out.success ? 'Templates imported successfully.' : 'Import failed.');
				const warnings = Array.isArray(out?.data?.warnings) ? out.data.warnings : [];
				const messageClass = out.success ? 'success' : 'error';

				if (importTemplatesResult) {
					importTemplatesResult.textContent = '';
					importTemplatesResult.classList.remove('error', 'success');
					importTemplatesResult.classList.add(messageClass);

					const messageSpan = document.createElement('span');
					messageSpan.textContent = message;
					importTemplatesResult.appendChild(messageSpan);

					if (warnings.length) {
						const warningList = document.createElement('ul');
						warningList.className = 'wprm-import-warning-list';

						warnings.forEach((warning) => {
							const listItem = document.createElement('li');
							listItem.textContent = warning;
							warningList.appendChild(listItem);
						});

						importTemplatesResult.appendChild(warningList);
					}
				}

				if (out.success) {
					fileInput.value = '';
				}
			} catch (error) {
				if (importTemplatesResult) {
					importTemplatesResult.textContent = 'Import failed. Please try again.';
					importTemplatesResult.classList.remove('success');
					importTemplatesResult.classList.add('error');
				}
				// eslint-disable-next-line no-console
				console.error('WPRM templates import failed', error);
			} finally {
				if (submitButton) {
					submitButton.disabled = false;
				}
			}
		});
	}
});
