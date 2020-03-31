import Vue from 'vue'
import Vuex from 'vuex'
import fetch from 'isomorphic-fetch'
console.log( 'trying hard' );

import CACGroupLibrary from './components/CACGroupLibrary.vue'

if ( ! global._babelPolyfill ) {
	//  require( '@babel/polyfill' )
}

Vue.use( Vuex )

function initialState() {
	const { libraryItemIds, libraryItems } = window.CACGroupLibrary;

	let state = {
		libraryItemIds,
		libraryItems,
	}
	console.log(state);

	return state
}

const store = new Vuex.Store(
	{
		state: initialState,

		mutations: {
			addUserToInviteByName( state, payload ) {
				let newUsersById               = Object.assign( {}, state.formInput.usersById )
				newUsersById[ payload.userId ] = payload.userName
				state.formInput.usersById      = newUsersById
			},

			setInitialState( state, payload ) {
				let newInviteableItems   = Object.assign( {}, payload.inviteableItems )
				let newInviteableItemIds = Object.assign( {}, payload.inviteableItemIds )

				state.inviteableItems = newInviteableItems

				if ( window.CACOModalStrings.currentGroup > 0 ) {
					const currentGroup = parseInt( window.CACOModalStrings.currentGroup )
					state.formInput.membershipItems.group.push( currentGroup )

					// Reorder inviteable groups so the invited one is first.
					const groupIndex = newInviteableItemIds.group.indexOf( currentGroup )
					if ( groupIndex > 0 ) {
						newInviteableItemIds.group.splice( groupIndex, 1 )
						newInviteableItemIds.group.unshift( currentGroup )
					}
				}

				if ( window.CACOModalStrings.currentSite > 0 ) {
					const currentSite = parseInt( window.CACOModalStrings.currentSite )
					state.formInput.membershipItems.site.push( currentSite )

					// Reorder inviteable site so the invited one is first.
					const siteIndex = newInviteableItemIds.site.indexOf( currentSite )
					if ( siteIndex > 0 ) {
						newInviteableItemIds.site.splice( siteIndex, 1 )
						newInviteableItemIds.site.unshift( currentSite )
					}
				}

				state.inviteableItemIds = newInviteableItemIds

				// Set default roles for each group and site.
				let newGroupRoles = {}
				let itemId
				for ( itemId in newInviteableItems.group ) {
					newGroupRoles[ itemId ] = 'member'
				}
				state.formInput.groupRoles = newGroupRoles

				let newSiteRoles = {}
				for ( itemId in newInviteableItems.site ) {
					newSiteRoles[ itemId ] = 'author'
				}
				state.formInput.siteRoles = newSiteRoles

				state.initialStateLoaded = true
			},

			showModal( state, payload ) {
				document.body.classList.add( 'noscroll' )
				state.modalIsVisible = true
			}
		},

		actions: {
			fetchInitialState( commit ) {
				const { endpointBase, nonce } = window.CACOModalStrings
				const endpoint                = endpointBase + 'app-config/'

				commit.commit(
					'setInitialStateIsLoading',
					{
						value: true
					}
				)

			return fetch(
				endpoint,
				{
					method: 'GET',
					credentials: 'include',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': nonce
					}
				}
			)
			.then(
				function(response) {
					commit.commit(
						'setInitialStateIsLoading',
						{
							value: false
						}
					)
					return response.json()
				}
			).then(
				function(json) {
					return json
				}
			).catch(
				function(ex) {
					console.log( 'failed', ex )
				}
			)
			}
		}
	}
)

window.cacGroupLibrary = new Vue(
	{
		el: '#cac-group-library',
			store,
		components: {
			app: CACGroupLibrary
		},
		render: h => h( 'app' ),
			/*
			mixins: [
			AjaxTools
			]
			*/
	}
);
