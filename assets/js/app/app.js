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
	const {
		addNewUrl, canCreateNew,
		libraryItemIds, libraryItems
	} = window.CACGroupLibrary;

	const currentItemType = 'any'
	const currentFolder = 'any'
	const currentSort = 'title'
	const currentSortOrder = 'asc'
	const currentPage = 1

	let state = {
		addNewUrl,
		canCreateNew,
		currentFolder,
		currentItemType,
		currentPage,
		currentSort,
		currentSortOrder,
		libraryItemIds,
		libraryItems,
	}

	return state
}

const store = new Vuex.Store(
	{
		state: initialState,

		mutations: {
			setSort( state, payload ) {
				const { newSort, newSortOrder } = payload
				const { libraryItems } = state

				let newItemIds = [...state.libraryItemIds]
				newItemIds.sort( function( a, b ) {
					const itemA = libraryItems[a]
					const itemB = libraryItems[b]

					switch ( newSort ) {
						case 'title' :
							const titleA = itemA.title
							const titleB = itemB.title

							if ( 'asc' === newSortOrder ) {
								return titleA.localeCompare( titleB )
							} else {
								return titleB.localeCompare( titleA )
							}

						case 'added-by' :
							const addedByA = itemA.user.name
							const addedByB = itemB.user.name

							if ( 'asc' === newSortOrder ) {
								return addedByA.localeCompare( addedByB )
							} else {
								return addedByB.localeCompare( addedByA )
							}

						case 'date' :
							const dateA = new Date( itemA.date_modified ).getTime()
							const dateB = new Date( itemB.date_modified ).getTime()
							console.log(dateA - dateB)

							if ( 'asc' === newSortOrder ) {
								return dateA - dateB
							} else {
								return dateB - dateA
							}
					}
				} )

				state.libraryItemIds = newItemIds

				state.currentSort = newSort
				state.currentSortOrder = newSortOrder
			},

			addUserToInviteByName( state, payload ) {
				let newUsersById               = Object.assign( {}, state.formInput.usersById )
				newUsersById[ payload.userId ] = payload.userName
				state.formInput.usersById      = newUsersById
			},

			setCurrentItemType( state, payload ) {
				state.currentItemType = payload.value
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
