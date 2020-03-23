import Vue from 'vue'
import Vuex from 'vuex'
import fetch from 'isomorphic-fetch'
console.log('trying hard');

import CACGroupLibrary from './components/CACGroupLibrary.vue'

if ( ! global._babelPolyfill ) {
//	require( '@babel/polyfill' )
}

Vue.use(Vuex)

const store = new Vuex.Store({
	state: {
		test: 1,
		completedSteps: {
			0: false,
			1: true,
			2: true,
			3: true
		},
		currentMembershipTab: 'sites',
		currentPanel: 0,
		formInput: {
			customMessage: '',
			membershipItems: {
				group: [],
				site: []
			},
			groupRoles: {},
			siteRoles: {},
			usersByEmail: [],
			usersByEmailRaw: '',
			usersById: {},
			usersByIdRaw: '',
		},
		initialStateIsLoading: false,
		initialStateLoaded: false,
		inviteableItemIds: {
			group: [],
			site: []
		},
		inviteableItems: {
			group: {},
			site: {}
		},
		panels: {
			selectPeople: {
				contentComponent: 'PanelSelectPeople',
				description: '',
				label: '1. Select People to Invite',
				navLabel: '1. Select People',
			},
			membership: {
				contentComponent: 'PanelMembership',
				description: 'Select the type of invitation you are sending',
				label: '2. Membership',
				navLabel: '2. Membership',
			},
			review: {
				contentComponent: 'PanelReview',
				description: '',
				label: '3. Review & Submit',
				navLabel: '3. Review & Submit',
			},
			confirmation: {
				contentComponent: 'PanelConfirmation',
				description: '',
				label: '4. Confirmation',
				navLabel: '4. Confirmation',
			},
		},
		modalIsVisible: false,
		panelNames: [
			'selectPeople',
			'membership',
			'review',
			'confirmation',
		],
		validFields: {
			inviteByEmail: true,
			inviteByName: true,
		}
	},

	mutations: {
		addUserToInviteByName( state, payload ) {
			let newUsersById = Object.assign( {}, state.formInput.usersById )
			newUsersById[ payload.userId ] = payload.userName
			state.formInput.usersById = newUsersById
		},

		setInitialState( state, payload ) {
			let newInviteableItems = Object.assign( {}, payload.inviteableItems )
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
			document.body.classList.add('noscroll')
			state.modalIsVisible = true
		}
	},

	actions: {
		fetchInitialState ( commit ) {
			const { endpointBase, nonce } = window.CACOModalStrings
			const endpoint = endpointBase + 'app-config/'

			commit.commit( 'setInitialStateIsLoading', {
				value: true
			} )

			return fetch( endpoint, {
				method: 'GET',
				credentials: 'include',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce
				}
			} )
			.then( function(response) {
				commit.commit( 'setInitialStateIsLoading', {
					value: false
				} )
				return response.json()
			}).then( function(json) {
				return json
			}).catch( function(ex) {
				console.log('failed', ex )
			})
		}
	}
})

window.cacGroupLibrary = new Vue( {
	el: '#cac-group-library',
	store,
	components: {
		app: CACGroupLibrary
	},
	render: h => h('app'),
	/*
	mixins: [
		AjaxTools
	]
	*/
} );
