import React, { Component, Fragment } from 'react';

import StarRatings from 'react-star-ratings';

import '../../../../css/admin/dashboard/feedback.scss';

import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';

import Block from '../../layout/Block';

export default class Feedback extends Component {
    constructor(props) {
        super(props);

        this.state = {
            rating: 0,
        }
    }

    render() {
        return (
            <Block
                title={ __wprm( 'Feedback' ) }
            >
                <div className="wprm-admin-dashboard-feedback-container">
                    {
                        0 === this.state.rating
                        &&
                        <Fragment>
                            <label>{ __wprm( 'How would you rate WP Recipe Maker?' ) }</label>
                            <div>
                                <StarRatings
                                    rating={ this.state.rating }
                                    starDimension="25px"
                                    starSpacing="0"
                                    starHoverColor="#2271b1"
                                    changeRating={ ( newRating ) => {
                                        this.setState({
                                            rating: newRating,
                                        }, () => {
                                            Api.utilities.giveFeedback( newRating );
                                        });
                                    } }
                                    numberOfStars={5}
                                    name='feedback'
                                />
                            </div>
                        </Fragment>
                    }
                    {
                        0 < this.state.rating
                        && 3 >= this.state.rating
                        &&
                        <Fragment>
                            <p>Sorry to hear you don't seem to be enjoying WP Recipe Maker as much as we want you to. It would be great if you could provide us with any feedback at all.</p>
                            <p>Email <a href="mailto:support@bootstrapped.ventures" target="_blank">support@bootstrapped.ventures</a> with any problems, frustrations or suggestions and we'll be happy to help you out! We improve the plugin weekly based on customer feedback, so your thoughts can really make a difference.</p>
                            <a
                                className="button button-primary"
                                href="mailto:support@bootstrapped.ventures"
                                target="_blank"
                            >Contact us now!</a>
                        </Fragment>
                    }
                    {
                        4 == this.state.rating
                        &&
                        <Fragment>
                            <p>Happy to hear that you do seem to be enjoying WP Recipe Maker! We are aiming to be a 5-star plugin though, so it would be amazing if you could give us any feedback at all on how we can get there for you.</p>
                            <p>Email <a href="mailto:support@bootstrapped.ventures" target="_blank">support@bootstrapped.ventures</a> with any problems, frustrations or suggestions and we'll be happy to help you out! We improve the plugin weekly based on customer feedback, so your thoughts can really make a difference.</p>
                            <a
                                className="button button-primary"
                                href="mailto:support@bootstrapped.ventures"
                                target="_blank"
                            >Contact us now!</a>
                        </Fragment>
                    }
                    {
                        5 == this.state.rating
                        &&
                        <Fragment>
                            <p>Very happy to hear you're enjoying WP Recipe Maker!</p>
                            <p>It would be amazing if you could help spread the word and leave an honest <a href="https://wordpress.org/support/plugin/wp-recipe-maker/reviews/#new-post" target="_blank">review over at wordpress.org</a> for our plugin. This really helps with getting new users, which leads to more customers and more time we can spend improving WPRM.</p>
                            <a
                                className="button button-primary"
                                href="https://wordpress.org/support/plugin/wp-recipe-maker/reviews/#new-post"
                                target="_blank"
                            >Leave a review!</a>
                            <p>Already left a review? Thanks a lot for taking the time! If you have any further feedback at all, feel free to contact us at <a href="mailto:support@bootstrapped.ventures" target="_blank">support@bootstrapped.ventures</a>. We improve the plugin weekly based on customer feedback, so your thoughts can really make a difference.</p>
                        </Fragment>
                    }
                </div>
            </Block>
        );
    }
}