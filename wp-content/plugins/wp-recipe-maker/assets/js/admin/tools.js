import '../../css/admin/tools.scss';

let action = false;
let args = {};
let posts = [];
let posts_total = 0;

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
			update_progress_bar();

			if (posts.length > 0) {
				await handle_posts();
			} else {
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

function update_progress_bar() {
	const percentage = (1.0 - posts.length / posts_total) * 100;
	const bar = document.querySelector('#wprm-tools-progress-bar');
	if (bar) {
		bar.style.width = `${percentage}%`;
	}
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
