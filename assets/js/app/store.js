import Vue from 'vue'
import Vuex from 'vuex'
import fetch from 'isomorphic-fetch'
import router from './router'

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
			description: '',
			folder: '',
			newFolderTitle: '',
		},
		folderNames: {
		},
	}
}

function initialState() {
	const {
		canCreateNew, canEditFolders, foldersOfGroup
	} = window.CACGroupLibrary;

	const currentSort = 'date'
	const currentSortOrder = 'desc'

	const isSearchExpanded = false

	const perPage = 20

	const showDescriptions = false

	const isLoading = false

	const forms = defaultFormsState()

	const route = {
		path: '/',
		query: {
			folder: 'any',
			itemType: 'any',
			page: 1,
			searchTerm: '',
		}
	}

	return {
		canCreateNew,
		canEditFolders,
		currentlyEditedFolder: '',
		currentSort,
		currentSortOrder,
		deleteInProgress: false,
		filteredItemIds: [],
		folderCounts: {},
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
		route,
		showDescriptions,
		silentUpdate: false,
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

			calculateFolderCounts( state ) {
				const { foldersOfGroup, libraryItemIds, libraryItems } = state

				let folderCounts = { 
					'_null': 0
				}

				foldersOfGroup.map(
					function( folderName ) {
						folderCounts[ folderName ] = 0
					}
				)

				libraryItemIds.map(
					function( itemId ) {
						if ( libraryItems.hasOwnProperty( itemId ) ) {
							const { folders } = libraryItems[ itemId ]
							if ( 'undefined' !== typeof folders && folders.length > 0 ) {
								folders.map(
									function( folderName ) {
										folderCounts[ folderName ]++
									}
								)
							} else {
								folderCounts._null++
							}
						}
					}
				)

				state.folderCounts = folderCounts
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
				const theCurrentItemType = state.route.query.hasOwnProperty( 'itemType' ) ? decodeURIComponent( state.route.query.itemType ) : 'any'
				if ( 'any' !== theCurrentItemType ) {
					newFilteredItemIds = newFilteredItemIds.filter(
						itemId => theCurrentItemType === state.libraryItems[ itemId ].item_type
					)
				}

				// Folder dropdown.
				const theCurrentFolder = state.route.query.hasOwnProperty( 'folder' ) ? decodeURIComponent( state.route.query.folder ) : 'any'
				if ( '_null' === theCurrentFolder ) {
					newFilteredItemIds = newFilteredItemIds.filter(
						function( itemId ) {
							return 'undefined' === typeof state.libraryItems[ itemId ].folders || state.libraryItems[ itemId ].folders.length === 0
						}
					)
				} else if ( 'any' !== theCurrentFolder ) {
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
				const theCurrentSearchTerm = state.route.query.hasOwnProperty( 'searchTerm' ) ? decodeURIComponent( state.route.query.searchTerm ).toLowerCase() : ''
				if ( '' !== theCurrentSearchTerm ) {
					newFilteredItemIds = newFilteredItemIds.filter(
						function( itemId ) {
							var theLibraryItem = state.libraryItems[ itemId ]
							var theItemTitle = theLibraryItem.hasOwnProperty( 'title' ) ? theLibraryItem.title : ''
							var theItemDescription = theLibraryItem.hasOwnProperty( 'description' ) ? theLibraryItem.description : ''
							var theItemAuthorName = theLibraryItem.hasOwnProperty( 'user' ) ? theLibraryItem.user.name : ''

							var matchTitle = -1 !== theItemTitle.toLowerCase().indexOf( theCurrentSearchTerm )
							var matchDescription = -1 !== theItemDescription.toLowerCase().indexOf( theCurrentSearchTerm )
							var matchAuthorName = -1 !== theItemAuthorName.toLowerCase().indexOf( theCurrentSearchTerm )

							return matchTitle || matchDescription || matchAuthorName
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
							const dateAString = itemA.date_modified.replace( ' ', 'T' )
							const dateBString = itemB.date_modified.replace( ' ', 'T' )

							const dateA = new Date( dateAString ).getTime()
							const dateB = new Date( dateBString ).getTime()

							if ( 'asc' === currentSortOrder ) {
								return dateA - dateB
							} else {
								return dateB - dateA
							}
					}
				} )

				state.filteredItemIds = newFilteredItemIds

				// Paginate.
				const { perPage, isSearchExpanded } = state
				const currentPage = state.route.query.hasOwnProperty( 'page' ) ? Number( state.route.query.page ) : 1
				const startNumber = ( perPage * ( currentPage - 1 ) )

				let newPaginatedItemIds = [...newFilteredItemIds]

				// Search results are not paginated.
				if ( ! isSearchExpanded ) {
					newPaginatedItemIds = newPaginatedItemIds.slice( startNumber, startNumber + perPage )
				}

				state.paginatedItemIds = newPaginatedItemIds
			},

			replaceFoldersOfGroup( state, payload ) {
				state.foldersOfGroup = payload
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

			setCurrentlyEditedFolder( state, payload ) {
				state.currentlyEditedFolder = payload.value
			},

			setDeleteInProgress( state, payload ) {
				state.deleteInProgress = payload.value
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

			setFolderNameFormValue( state, payload ) {
				const { folderName, field, value } = payload

				let newThisFolder = Object.assign( {}, state.forms.folderNames[ folderName ] )
				newThisFolder[ field ] = value

				let newFolderNames = Object.assign( {}, state.forms.folderNames )
				newFolderNames[ folderName ] = newThisFolder

				let newForms = Object.assign( {}, state.forms )
				Vue.set( newForms, 'folderNames', newFolderNames )

				state.forms = newForms
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

			setPerPage( state, payload ) {
				state.perPage = payload.value
			},

			setShowDescriptions( state, payload ) {
				state.showDescriptions = payload.value
			},

			setSilentUpdate( state, payload ) {
				state.silentUpdate = payload.value
			},

			setSubmitInProgress( state, payload ) {
				state.submitInProgress = payload.value
			},

			setSuccessMessage( state, payload ) {
				state.successMessage = payload.value
			},

			setUpFolderNamesForm( state, payload ) {
				const { foldersOfGroup } = state

				let newFolderNamesForm = {
					'_new': { savedValue: '', editValue: '' }
				}

				foldersOfGroup.map(
					function( folderName ) {
						newFolderNamesForm[ folderName ] = { savedValue: folderName, editValue: folderName }
					}
				)

				let newForms = Object.assign( {}, state.forms )
				Vue.set( newForms, 'folderNames', newFolderNamesForm )

				state.forms = newForms
			},

			setValidationError( state, payload ) {
				const { nodeName, message } = payload

				state.validationErrors = Object.assign( {}, state.validationErrors, {
					[nodeName]: message
				} )
			},
		},

		actions: {
			addFolder( commit, data ) {
				const { endpointBase, groupId, nonce } = window.CACGroupLibrary

				const { editValue } = commit.state.forms.folderNames._new

				const endpoint = endpointBase + 'folders-of-group?groupId=' + groupId

				const body = {
					editValue,
					folderName: '_new'
				}

				const headers = {
						'X-WP-Nonce': nonce,
						'Content-Type': 'application/json'
				}

				return fetch(
					endpoint,
					{
						method: 'POST',
						credentials: 'include',
						headers,
						body: JSON.stringify( body ),
					}
				)
			},

			deleteFolder( commit, data ) {
				const { endpointBase, groupId, nonce } = window.CACGroupLibrary
				const { deleteType, folderName } = data

				const endpoint = endpointBase + 'folders-of-group?groupId=' + groupId

				const body = {
					deleteType,
					folderName
				}

				const headers = {
						'X-WP-Nonce': nonce,
						'Content-Type': 'application/json'
				}

				return fetch(
					endpoint,
					{
						method: 'POST',
						credentials: 'include',
						headers,
						body: JSON.stringify( body ),
					}
				)
			},

			deleteItem( commit, itemIdToDelete ) {
				const { endpointBase, nonce } = window.CACGroupLibrary

				const endpoint = endpointBase + 'library-items/' + itemIdToDelete + '/'

				const headers = {
						'X-WP-Nonce': nonce,
						'Content-Type': 'application/json',
				}

				return fetch(
					endpoint,
					{
						method: 'DELETE',
						credentials: 'include',
						headers,
					}
				)
			},

			fetchFoldersOfGroup( {commit} ) {
				const { endpointBase, groupId, nonce } = window.CACGroupLibrary

				const endpoint = endpointBase + 'folders-of-group?groupId=' + groupId

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
						commit( 'replaceFoldersOfGroup', json.results )
						commit( 'calculateFolderCounts' )
					}
				} )
			},

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
				const { silentUpdate } = commit.state
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
					body.append( 'silentUpdate', silentUpdate ? 1 : 0 )
					contentType = ''
				} else {
					body = Object.assign( {}, commit.state.forms[ itemType ], {
						itemType,
						groupId,
						silentUpdate
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
			},

			updateFolderName( commit ) {
				const { currentlyEditedFolder } = commit.state
				const { editValue } = commit.state.forms.folderNames[ currentlyEditedFolder ]

				const { endpointBase, groupId, nonce } = window.CACGroupLibrary

				const endpoint = endpointBase + 'folders-of-group?groupId=' + groupId

				const body = {
					editValue,
					folderName: currentlyEditedFolder,
				}

				const headers = {
						'X-WP-Nonce': nonce,
						'Content-Type': 'application/json'
				}

				return fetch(
					endpoint,
					{
						method: 'POST',
						credentials: 'include',
						headers,
						body: JSON.stringify( body ),
					}
				)
			}
		}
	}
)
