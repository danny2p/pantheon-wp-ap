import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/add-recipe-to-post.scss';

import { __wprm } from 'Shared/Translations';
import Header from '../general/Header';
import Footer from '../general/Footer';
import SelectPost from '../select/SelectPost';
import Api from 'Shared/Api';

export default class AddRecipeToPost extends Component {
    constructor(props) {
        super(props);

        this.state = {
            selectedPost: false,
            creatingPost: false,
            addingToPost: false,
        };

        this.handleAddToExistingPost = this.handleAddToExistingPost.bind(this);
        this.handleDoNothing = this.handleDoNothing.bind(this);
    }

    handleCreateNewPost(redirectToEdit = true) {
        if (this.state.creatingPost) {
            return;
        }

        const recipeId = this.props.args.recipeId;

        if (!recipeId) {
            return;
        }

        this.setState({ creatingPost: true }, () => {
            Api.recipe.createPostForRecipe(recipeId).then((data) => {
                if (data && data.editLink) {
                    if (redirectToEdit) {
                        window.location.href = data.editLink;
                    } else {
                        // Stay on Manage page
                        this.setState({ creatingPost: false });
                        this.props.maybeCloseModal();
                    }
                } else {
                    this.setState({ creatingPost: false });
                    alert(__wprm('Failed to create post. Please try again.'));
                }
            });
        });
    }

    handleAddToExistingPost() {
        if (this.state.addingToPost || !this.state.selectedPost) {
            return;
        }

        const recipeId = this.props.args.recipeId;
        const postId = this.state.selectedPost.id;

        if (!recipeId || !postId) {
            return;
        }

        this.setState({ addingToPost: true }, () => {
            Api.recipe.addRecipeToPost(recipeId, postId).then((data) => {
                if (data && data.editLink) {
                    window.location.href = data.editLink;
                } else {
                    this.setState({ addingToPost: false });
                    alert(__wprm('Failed to add recipe to post. Please try again.'));
                }
            });
        });
    }

    handleDoNothing() {
        this.props.maybeCloseModal();
    }

    render() {
        return (
            <Fragment>
                <Header
                    onCloseModal={this.props.maybeCloseModal}
                >
                    {__wprm('Add Recipe to Post')}
                </Header>
                <div className="wprm-admin-modal-add-recipe-to-post-container">
                    <p>
                        {__wprm('What would you like to do with this new')}
                        {this.props.args.recipeName ? ` "${this.props.args.recipeName}"` : ''}
                        {__wprm(' recipe?')}
                    </p>
                    
                    <div className="wprm-admin-modal-add-recipe-to-post-options">
                        <div className="wprm-admin-modal-add-recipe-to-post-section">
                            <h3>{__wprm('Add recipe to a new post')}</h3>
                            <div className="wprm-admin-modal-add-recipe-to-post-buttons">
                                <button
                                    className="button button-primary"
                                    onClick={() => this.handleCreateNewPost(true)}
                                    disabled={this.state.creatingPost || this.state.addingToPost}
                                >
                                    {this.state.creatingPost ? __wprm('Creating...') : __wprm('Create new post and edit')}
                                </button>
                                <button
                                    className="button button-secondary"
                                    onClick={() => this.handleCreateNewPost(false)}
                                    disabled={this.state.creatingPost || this.state.addingToPost}
                                >
                                    {this.state.creatingPost ? __wprm('Creating...') : __wprm('Create new post and stay here')}
                                </button>
                            </div>
                        </div>

                        <div className="wprm-admin-modal-add-recipe-to-post-section">
                            <h3>{__wprm('Add recipe to an existing post')}</h3>
                            <div className="wprm-admin-modal-add-recipe-to-post-existing">
                                <SelectPost
                                    value={this.state.selectedPost}
                                    onValueChange={(selectedPost) => {
                                        this.setState({ selectedPost });
                                    }}
                                />
                                <button
                                    className="button button-secondary"
                                    onClick={this.handleAddToExistingPost}
                                    disabled={!this.state.selectedPost || this.state.addingToPost || this.state.creatingPost}
                                >
                                    {this.state.addingToPost ? __wprm('Adding...') : __wprm('Add to Post')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <Footer savingChanges={this.state.creatingPost || this.state.addingToPost}>
                    <button
                        className="button"
                        onClick={this.handleDoNothing}
                        disabled={this.state.creatingPost || this.state.addingToPost}
                    >
                        {__wprm('Do not do anything')}
                    </button>
                </Footer>
            </Fragment>
        );
    }
}
