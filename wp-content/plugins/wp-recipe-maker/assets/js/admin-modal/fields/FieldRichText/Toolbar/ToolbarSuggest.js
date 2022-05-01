import React, { Component, Fragment } from 'react';
import he from 'he';

import Api from 'Shared/Api';
import Loader from 'Shared/Loader';
import { __wprm } from 'Shared/Translations';

export default class ToolbarSuggest extends Component {
    constructor(props) {
		super(props);
		
		// Cache suggestions.
		window.wprm_admin_modal_suggestions = window.wprm_admin_modal_suggestions || {};
		if ( ! window.wprm_admin_modal_suggestions.hasOwnProperty( props.type ) ) {
			window.wprm_admin_modal_suggestions[ props.type ] = {};
		}

        this.state = {
			search: '',
			suggestions: [],
			loading: false,
		}
	}

	componentDidMount() {
		this.updateSuggestions( this.props.value );
	}

	componentDidUpdate() {
		if ( this.props.value !== this.state.search ) {
			this.updateSuggestions( this.props.value );
		}
	}
	
	updateSuggestions( search ) {
		if ( window.wprm_admin_modal_suggestions[ this.props.type ].hasOwnProperty( search ) ) {
			this.setState({
				suggestions: window.wprm_admin_modal_suggestions[ this.props.type ][ search ],
				search,
			});
		} else {
			this.setState({
				loading: true,
				search,
			});
	
			Api.modal.getSuggestions({
				type: this.props.type,
				search
			}).then(data => {
				if ( data ) {
					window.wprm_admin_modal_suggestions[ this.props.type ][ search ] = data.suggestions;

					this.setState({
						suggestions: data.suggestions,
						loading: false,
					});
				}
			});
		}
	}
  
    render() {
        return (
            <div className="wprm-admin-modal-toolbar-suggest">
				{
					! this.state.loading
					&& 0 === this.state.suggestions.length
					?
					<strong>{ __wprm( 'No suggestions found.' ) }</strong>
					:
					<Fragment>
						<strong>{ __wprm( 'Suggestions:' ) }</strong>
						{
							this.state.loading
							?
							<Loader/>
							:
							<Fragment>
								{
									this.state.suggestions.map((suggestion, index) => (
										<span
											className="wprm-admin-modal-toolbar-suggestion"
											onMouseDown={ (event) => {
												event.preventDefault();
												this.props.onSelect( suggestion.name );
											} }
											key={ index }
										>
											<span className="wprm-admin-modal-toolbar-suggestion-text">{ he.decode( suggestion.name ) } ({ suggestion.count})</span>
										</span>
									))
								}
							</Fragment>
						}
					</Fragment>
				}
			</div>
        );
    }
}
