import Vue from 'vue'
import Vuex from 'vuex'
import fetch from 'isomorphic-fetch'

Vue.use(Vuex)

function defaultFormsState() {
	return {
		itemTypeSelector: '',
		bpDoc: {
			itemId: 0,
			title: '',
			content: '',
			folder: '',
			newFolderTitle: '',
			parent: 0,
		},
		bpGroupDocument: {
			itemId: 0,
			title: '',
			description: '',
			folder: '',
			newFolderTitle: '',
			file: '',
		},
		externalLink: {
			itemId: 0,
			title: '',
			url: '',
			folder: '',
			newFolderTitle: '',
		}
	}
}

function initialState() {
	const {
		canCreateNew, foldersOfGroup
	} = window.CACGroupLibrary;

	const currentItemType = 'any'
	const currentFolder = 'any'
	const currentSort = 'title'
	const currentSortOrder = 'asc'

	const currentSearchTerm = ''
	const isSearchExpanded = false

	const currentPage = 1
	const perPage = 20

	const showDescriptions = false

	const isLoading = false

	const forms = defaultFormsState()

	return {
		canCreateNew,
		currentFolder,
		currentItemType,
		currentPage,
		currentSearchTerm,
		currentSort,
		currentSortOrder,
		filteredItemIds: [],
		foldersOfGroup,
		forms,
		initialLoadComplete: false,
		isLoading,
		isSearchExpanded,
		libraryItemIds: [],
		libraryItems: {},
		paginatedItemIds: [],
		perPage,
		potentialParentDocs: [],
		showDescriptions,
		successMessage: '',
		submitInProgress: false,
		validationErrors: {},
		visitedFields: {}
	}
}

export default new Vuex.Store(
	{
		state: initialState(),

		mutations: {
			setSort( state, payload ) {
				const { newSort, newSortOrder } = payload
				const { libraryItems } = state

				state.currentSort = newSort
				state.currentSortOrder = newSortOrder
			},

			addUserToInviteByName( state, payload ) {
				let newUsersById               = Object.assign( {}, state.formInput.usersById )
				newUsersById[ payload.userId ] = payload.userName
				state.formInput.usersById      = newUsersById
			},

			refresh( state ) {
				state.isLoading = true

				this.commit( 'refreshFilteredItemIds' )

				setTimeout(
					function() {
						state.isLoading = false
					},
					250
				)
			},

			/**
			 * Master method for applying current filter + sort + pagination.
			 *
			 * In the following order:
			 * - List of all libraryItemIds belonging to group is filtered using currentItemType,
			 *   then currentFolder. This list is stored as filteredItemIds, and is used for
			 *   things like the total item count in pagination (viewing 1-10 of 25 items, etc)
			 * - filteredItemIds is sorted according to currentSort and currentSortBy
			 * - Pagination is applied on the filtered and sorted items. The IDs of the items displayed
			 *   in the current set of results is stored as paginatedItemIds.
			 */
			refreshFilteredItemIds( state ) {
				let newFilteredItemIds = [...state.libraryItemIds]

				// Item type dropdown.
				const theCurrentItemType = state.currentItemType
				if ( 'any' !== theCurrentItemType ) {
					newFilteredItemIds = newFilteredItemIds.filter(
						itemId => theCurrentItemType === state.libraryItems[ itemId ].item_type
					)
				}

				// Folder dropdown.
				const theCurrentFolder = state.currentFolder
				if ( 'any' !== theCurrentFolder ) {
					newFilteredItemIds = newFilteredItemIds.filter(
						function( itemId ) {
							for ( var i in state.libraryItems[ itemId ].folders ) {
								if ( theCurrentFolder === state.libraryItems[ itemId ].folders[ i ] ) {
									return true
								}
							}
							return false
						}
					)
				}

				// Search.
				const theCurrentSearchTerm = state.currentSearchTerm.toLowerCase()
				if ( '' !== theCurrentSearchTerm ) {
					newFilteredItemIds = newFilteredItemIds.filter(
						function( itemId ) {
							var theLibraryItem = state.libraryItems[ itemId ]
							var theItemTitle = theLibraryItem.hasOwnProperty( 'title' ) ? theLibraryItem.title : ''
							var theItemDescription = theLibraryItem.hasOwnProperty( 'description' ) ? theLibraryItem.description : ''

							var matchTitle = -1 !== theItemTitle.toLowerCase().indexOf( theCurrentSearchTerm )
							var matchDescription = -1 !== theItemDescription.toLowerCase().indexOf( theCurrentSearchTerm )

							return matchTitle || matchDescription
						}
					)
				}

				// Sort.
				const { currentSort, currentSortOrder, libraryItems } = state
				newFilteredItemIds.sort( function( a, b ) {
					const itemA = libraryItems[a]
					const itemB = libraryItems[b]

					switch ( currentSort ) {
						case 'title' :
							const titleA = itemA.title
							const titleB = itemB.title

							if ( 'asc' === currentSortOrder ) {
								return titleA.localeCompare( titleB )
							} else {
								return titleB.localeCompare( titleA )
							}

						case 'added-by' :
							const addedByA = itemA.user.name
							const addedByB = itemB.user.name

							if ( 'asc' === currentSortOrder ) {
								return addedByA.localeCompare( addedByB )
							} else {
								return addedByB.localeCompare( addedByA )
							}

						case 'date' :
							const dateA = new Date( itemA.date_modified ).getTime()
							const dateB = new Date( itemB.date_modified ).getTime()

							if ( 'asc' === currentSortOrder ) {
								return dateA - dateB
							} else {
								return dateB - dateA
							}
					}
				} )

				state.filteredItemIds = newFilteredItemIds

				// Paginate.
				const { perPage, currentPage, isSearchExpanded } = state
				const startNumber = ( perPage * ( currentPage - 1 ) )

				let newPaginatedItemIds = [...newFilteredItemIds]

				// Search results are not paginated.
				if ( ! isSearchExpanded ) {
					newPaginatedItemIds = newPaginatedItemIds.slice( startNumber, startNumber + perPage )
				}

				state.paginatedItemIds = newPaginatedItemIds
			},

			replaceItems( state, payload ) {
				state.libraryItems = payload
				state.libraryItemIds = Object.keys(payload)
			},

			replacePotentialParentDocs( state, payload ) {
				state.potentialParentDocs = payload
			},

			resetForms( state ) {
				state.forms = defaultFormsState()
			},

			resetValidationErrors( state ) {
				state.validationErrors = {}
			},

			setCurrentFolder( state, payload ) {
				state.currentFolder = payload.value
			},

			setCurrentItemType( state, payload ) {
				state.currentItemType = payload.value
			},

			setCurrentPage( state, payload ) {
				state.currentPage = payload.value
			},

			setCurrentSearchTerm( state, payload ) {
				state.currentSearchTerm = payload.value
			},

			setFieldHasBeenVisited( state, payload ) {
				const { formName, fieldName } = payload

				let newVisitedFields = Object.assign( {}, state.visitedFields )

				if ( ! newVisitedFields.hasOwnProperty( formName ) ) {
					newVisitedFields[ formName ] = []
				}

				newVisitedFields[ formName ].push( fieldName )

				state.visitedFields = newVisitedFields
			},

			setFormFieldValue( state, payload ) {
				const { form, field, value } = payload

				let newForm  = Object.assign( {}, state.forms[ form ] )

				Vue.set( newForm, field, value )

				let newForms = Object.assign( {}, state.forms )
				Vue.set( newForms, form, newForm )

				state.forms = newForms
			},

			setInitialLoadComplete( state ) {
				state.initialLoadComplete = true
			},

			setItemTypeSelector( state, payload ) {
				state.forms.itemTypeSelector = payload.value
			},

			setIsLoading( state, payload ) {
				state.isLoading = payload.value
			},

			setIsSearchExpanded( state, payload ) {
				state.isSearchExpanded = payload.value
			},

			setShowDescriptions( state, payload ) {
				state.showDescriptions = payload.value
			},

			setSubmitInProgress( state, payload ) {
				state.submitInProgress = payload.value
			},

			setSuccessMessage( state, payload ) {
				state.successMessage = payload.value
			},

			setValidationError( state, payload ) {
				const { nodeName, message } = payload

				state.validationErrors = Object.assign( {}, state.validationErrors, {
					[nodeName]: message
				} )
			},
		},

		actions: {
			fetchPotentialParentDocs( {commit} ) {
				const { endpointBase, groupId, nonce } = window.CACGroupLibrary

				const endpoint = endpointBase + 'potential-parent-docs?groupId=' + groupId

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
				.then( function( response ) {
					return response.json()
				} )
				.then( function ( json )  {
					if ( json.success ) {
						commit( 'replacePotentialParentDocs', json.results )
					}
				} )
			},

			refetchItems( {commit} ) {
				const { endpointBase, groupId, nonce } = window.CACGroupLibrary

				const endpoint = endpointBase + 'library-items?groupId=' + groupId

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
				.then( function( response ) {
					return response.json()
				} )
				.then( function ( json )  {
					if ( json.success ) {
						commit( 'replaceItems', json.results )
						commit( 'refreshFilteredItemIds' )
					}
				} )
			},

			submitItem( commit ) {
				const { endpointBase, groupId, nonce } = window.CACGroupLibrary

				const itemType = commit.state.forms.itemTypeSelector
				const { itemId } = commit.state.forms[ itemType ]
				const isEdit = itemId > 0

				let body, contentType
				if ( 'bpGroupDocument' === itemType ) {
					body = new FormData()

					for ( var fieldName in commit.state.forms[ itemType ] ) {
						body.append( fieldName, commit.state.forms[ itemType ][ fieldName ] )
					}

					body.append( 'itemType', itemType )
					body.append( 'groupId', groupId )
					contentType = ''
				} else {
					body = Object.assign( {}, commit.state.forms[ itemType ], {
						itemType,
						groupId
					} )
					body = JSON.stringify( body )
					contentType = 'application/json'
				}

				let endpoint
				if ( isEdit ) {
					endpoint = endpointBase + 'library-items/' + itemId + '/'
				} else {
					endpoint = endpointBase + 'library-items'
				}

				let headers = {
						'X-WP-Nonce': nonce
				}

				if ( contentType.length > 0 ) {
					headers['Content-Type'] = contentType
				}

				return fetch(
					endpoint,
					{
						method: 'POST',
						credentials: 'include',
						headers,
						body,
					}
				)
			}
		}
	}
)
