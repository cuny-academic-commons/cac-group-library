import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

function initialState() {
	const {
		addNewUrl, canCreateNew,
		libraryItemIds, libraryItems
	} = window.CACGroupLibrary;

	const currentItemType = 'any'
	const currentFolder = 'any'
	const currentSort = 'title'
	const currentSortOrder = 'asc'

	const currentSearchTerm = ''
	const isSearchExpanded = false

	const currentPage = 1
	const perPage = 20

	const filteredItemIds = libraryItemIds
	const paginatedItemIds = libraryItemIds

	const showDescriptions = false

	const isLoading = false

	let state = {
		addNewUrl,
		canCreateNew,
		currentFolder,
		currentItemType,
		currentPage,
		currentSearchTerm,
		currentSort,
		currentSortOrder,
		filteredItemIds,
		isLoading,
		isSearchExpanded,
		libraryItemIds,
		libraryItems,
		paginatedItemIds,
		perPage,
		showDescriptions,
	}

	return state
}

export default new Vuex.Store(
	{
		state: initialState,

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
								if ( theCurrentFolder === state.libraryItems[ itemId ].folders[ i ].slug ) {
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

							var matchTitle = -1 !== theLibraryItem.title.toLowerCase().indexOf( theCurrentSearchTerm )
							var matchDescription = -1 !== theLibraryItem.description.toLowerCase().indexOf( theCurrentSearchTerm )

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

			setIsLoading( state, payload ) {
				state.isLoading = payload.value
			},

			setIsSearchExpanded( state, payload ) {
				state.isSearchExpanded = payload.value
			},

			setShowDescriptions( state, payload ) {
				state.showDescriptions = payload.value
			},
		}
	}
)
