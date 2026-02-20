<template>
	<div id="cac-group-library-inner" class="cac-group-library-inner">
		<div :class="headerClass">
			<div class="header-filters">
				<ItemTypeFilterDropdown />
				<FolderFilterDropdown />
				<IncludeForumAttachmentsCheckbox />
			</div>

			<div class="header-right">
				<router-link
					class="add-new-item-button cac-button cac-button-secondary"
					v-if='canCreateNew'
					to='/new'
				>Add New Item</router-link>

				<SearchInput />
			</div>
		</div>

		<transition name="fade">
			<div v-if="successMessage.length > 0" class="success-message" v-html="successMessage">
				{{ successMessage }}
			</div>
		</transition>

		<transition name="fade">
			<div v-if="! isLoading && initialLoadComplete" class="group-library-refreshable">
				<div class="group-library-nav">
					<div
						class="current-folder-breadcrumb"
						v-if="'any' !== currentFolder"
					>{{currentFolderBreadcrumb}}</div>
				</div>

				<div
					class="group-library-items"
					id="group-library-items"
					v-if="showItemList"
				>
					<div class="group-library-column-headers group-library-row">
						<div class="group-library-column-header group-library-item-toggle">&nbsp;</div>

						<SortableColumnHeader
							label="File name"
							name="title"
							defaultSortOrder="asc"
						/>

						<div class="group-library-column-header group-library-item-details">
							Details
						</div>

						<div class="group-library-column-header group-library-item-tagged">
							Tagged
						</div>

						<SortableColumnHeader
							label="Date uploaded"
							name="date"
							defaultSortOrder="desc"
						/>

						<SortableColumnHeader
							label="Added by"
							name="added-by"
							defaultSortOrder="asc"
						/>
					</div>

					<ul class="group-library-items-list">
						<li v-for="itemId in paginatedItemIds">
							<LibraryItem
								:itemId='itemId'
							/>
						</li>
					</ul>
				</div>

				<div
					class="group-library-error-notice"
					v-if="libraryIsEmpty"
				>
					The library is empty.
					<router-link
						v-if='canCreateNew'
						to='/new'
					>Add an item</router-link>
				</div>

				<div
					class="group-library-error-notice"
					v-if="filteredListIsEmpty"
				>
					No items found. Try a different filter option or search query.
				</div>

				<button
					class="library-load-more-button"
					v-if="showLoadMore"
					v-on:click="doLoadMore()"
				>Load More</button>

				<div
					class="group-library-pagination"
					v-if="! showLoadMore"
				>
					<transition name="fade" mode="out-in">
						<SearchResultsCount v-if="isSearchExpanded" key="search-results" />
						<Pagination v-else key="pagination" :showPagLinks="true" />
					</transition>
				</div>
			</div>

			<div v-if="! initialLoadComplete" class="library-loading">
				Loading...
			</div>
		</transition>
	</div>
</template>

<script>
	import DescriptionToggle from '../components/DescriptionToggle.vue'
	import FolderFilterDropdown from '../components/FilterDropdowns/FolderFilterDropdown.vue'
	import IncludeForumAttachmentsCheckbox from '../components/IncludeForumAttachmentsCheckbox.vue'
	import ItemTypeFilterDropdown from '../components/FilterDropdowns/ItemTypeFilterDropdown.vue'
	import LibraryItem from '../components/LibraryItem.vue'
	import Pagination from '../components/Pagination.vue'
	import SearchInput from '../components/SearchInput.vue'
	import SearchResultsCount from '../components/SearchResultsCount.vue'
	import SortableColumnHeader from '../components/SortableColumnHeader.vue'

	export default {
		components: {
			DescriptionToggle,
			FolderFilterDropdown,
			IncludeForumAttachmentsCheckbox,
			ItemTypeFilterDropdown,
			LibraryItem,
			Pagination,
			SearchInput,
			SearchResultsCount,
			SortableColumnHeader
		},

		computed: {
			canCreateNew() {
				return this.$store.state.canCreateNew
			},

			currentFolder() {
				return this.$store.state.route.query.hasOwnProperty( 'folder' ) ? decodeURIComponent( this.$store.state.route.query.folder ) : 'any'
			},

			currentFolderBreadcrumb() {
				if ( '_null' === this.currentFolder ) {
					return 'Items not in a folder'
				} else {
					return 'In: ' + this.currentFolder
				}
			},

			errorNotice() {
				return
			},

			filteredListIsEmpty() {
				return ! this.libraryIsEmpty && this.paginatedItemIds.length === 0
			},

			headerClass() {
				let itemClasses = [ 'group-library-header' ]

				if ( this.isSearchExpanded ) {
					itemClasses.push( 'search-is-expanded' )
				}

				return itemClasses.join( ' ' )
			},

			initialLoadComplete() {
				return this.$store.state.initialLoadComplete
			},

			isSearchExpanded: {
				get() {
					return this.$store.state.isSearchExpanded
				},

				set( value ) {
					this.$store.commit(
						'setIsSearchExpanded',
						{ value }
					)

					this.$store.commit(
						'setShowDescriptions',
						{ value }
					)
				}
			},

			libraryIsEmpty() {
				return this.$store.state.libraryItemIds.length === 0
			},

			paginatedItemIds() {
				return this.$store.state.paginatedItemIds
			},

			isLoading() {
				return this.$store.state.isLoading
			},

			showDescriptionToggle() {
				for ( var itemId of this.paginatedItemIds ) {
					if ( this.hasDescription( itemId ) ) {
						return true
					}

					if ( ! this.currentFolder && this.hasFolders( itemId ) ) {
						return true
					}
				}

				return false
			},

			showEditColumn() {
				for ( var itemId of this.paginatedItemIds ) {
					if ( this.canEdit( itemId ) ) {
						return true
					}
				}

				return false
			},

			showItemList() {
				return this.paginatedItemIds.length > 0
			},

			showLoadMore() {
				if ( this.$mq !== 'mobile' ) {
					return false
				}

				return this.paginatedItemIds.length < this.$store.state.filteredItemIds.length
			},

			successMessage() {
				return this.$store.state.successMessage
			}
		},

		created() {
			const vm = this

			if ( this.initialLoadComplete ) {
				return;
			}

			setTimeout(
				function() {
					const templateNotice = document.getElementById( 'message' )

					if ( 'undefined' === typeof templateNotice || null === templateNotice ) {
						return
					}

					if ( 0 === templateNotice.childNodes.length ) {
						return
					}

					const noticeText = templateNotice.childNodes[0].innerHTML

					vm.$store.commit(
						'setSuccessMessage',
						{ value: noticeText }
					)

					setTimeout( function() {
						vm.$store.commit(
							'setSuccessMessage',
							{
								value: ''
							}
						)
					}, 10000 )
				},
				1000
			);

			this.$store.dispatch( 'refetchItems' )
				.then( function() {
					vm.$store.commit( 'refreshFilteredItemIds' )
					vm.$store.commit( 'setInitialLoadComplete' )
					vm.$store.commit( 'calculateFolderCounts' )
				} )
		},

		watch: {
			'$route.query.includeForumAttachments'( newValue, oldValue ) {
				if ( newValue !== oldValue ) {
					// Use the refresh mutation which handles the loading state and fade transition
					this.$store.commit( 'refresh' )
				}
			}
		},

		methods: {
			canEdit( itemId ) {
				const { can_edit } = this.$store.state.libraryItems[ itemId ]
				return !! can_edit
			},

			doLoadMore() {
				const { perPage } = this.$store.state

				this.$store.commit(
					'setPerPage',
					{
						value: perPage + 20,
					}
				)

				this.$store.commit( 'refreshFilteredItemIds' )
			},

			hasDescription( itemId ) {
				const { description, item_type } = this.$store.state.libraryItems[ itemId ]
				return 'forum_attachment' === item_type || ( 'string' === typeof description && description.length > 0 )
			},

			hasFolders( itemId ) {
				const { folders } = this.$store.state.libraryItems[ itemId ]
				return 'undefined' !== typeof folders && folders.length > 0
			},

			onAddNewClick() {
				return
			}
		}
	}
</script>

<style>
body.groups.single-item.library #item-header {
	display: none;
}

.library-loading {
	margin-top: 24px;
}

.header-filters {
	display: flex;
	gap: 50px;
}

#cac-group-library-inner {
	margin-top: 24px;
}

.cac-group-library-inner {
	font-family: var(--sans-serif);
}

.cac-group-library .vs__dropdown-toggle {
	border-radius: 0;
	padding: 8px 0 12px;
}

.vs__dropdown-option {
	font-family: var(--sans-serif);
}

.vs__dropdown-menu {
	width: auto !important;
}

.group-library-header {
	display: flex;
	gap: 50px;
	padding-bottom: 20px;
}

.header-right {
	align-items: center;
	display: flex;
	flex: 1;
	gap: 20px;
	justify-content: space-between;
}

.library-search {
	flex: 1;
}

.cac-button.add-new-item-button {
	line-height: 2em;
	padding: 8px 16px;
}

.group-library-header.search-is-expanded .header-actions {
	margin-right: 7px;
}

.group-library-nav:not(:empty) {
	padding: 12px 48px;
}

.group-library-pagination {
	padding-top: 20px;
}

.group-library-pagination > span {
	align-items: center;
	display: flex;
	justify-content: space-between;
}

.group-library-column-headers {
	background: #fff;

	/* Make the 'File name' text look like it's aligned with the arrow */
	.group-library-item-title {
		margin-left: -24px;
		padding-right: 50px; /* other columns need to align */
	}
}

ul.group-library-items-list {
	background: #fff;
	list-style-type: none;
	padding: 0;
	margin: 0;
}

ul.group-library-items-list li:nth-child(odd) {
	background: var(--xlt-grey);
}

.group-library-row {
	align-items: center;
	border-bottom: 1px solid var(--med-grey);
	display: flex;
	gap: 24px;
	padding: 12px 48px 12px 24px;
	position: relative;
}

.group-library-item-title {
	align-items: center;
	display: flex;
	gap: 12px;
	flex: 0 0 30%;
	overflow-wrap: anywhere;
	padding: 0;
}

@media screen and (max-width: 768px) {
	.group-library-row {
		padding: 12px 24px;
	}

	.group-library-items-list .group-library-row:first-child {
		border-bottom: none;
	}

	.group-library-item-title {
		flex: 0 0 40%;
	}

	.group-library-item-details,
	.group-library-item-tagged {
		display: none;
	}
}

@media screen and (min-width: 769px) and (max-width: 1024px) {
	.group-library-item-title {
		flex: 0 0 25%;
	}

	.group-library-item-details {
		flex: 0 0 18%;
	}

	.group-library-item-tagged {
		flex: 0 0 15%;
	}

	.group-library-item-date {
		flex: 0 0 12%;
	}

	.group-library-item-added-by {
		flex: 0 0 12%;
	}
}

.group-library-item-details {
	color: var(--dark-grey);
	flex: 0 0 20%;
}

.group-library-item-tagged {
	color: var(--dark-grey);
	flex: 0 0 15%;

	a {
		color: var(--dark-grey);
		text-decoration: underline !important;

		&:hover {
			text-decoration: none !important;
		}
	}
}

.group-library-item-added-by {
	color: var(--dark-grey);
	flex: 0 0 15%;
}

.directory-content .group-library-item-added-by a {
	color: var(--dark-grey);
	text-decoration: underline;
}

.directory-content .group-library-item-added-by a:hover {
	text-decoration: none;
}

.group-library-item-date {
	color: var(--dark-grey);
	flex: 0 0 15%;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}

.group-library-filters {
	position: relative;
	padding-right: 10px;
}

.description-toggle {
	position: absolute;
	right: 0;
	bottom: 0;
}

.group-library-refreshable {
}

.library-load-more-button {
	background: #fff;
	border: 1px solid #1c576c;
	color: #1C576C;
	font-size: 16px;
	padding: 9px 15px;
	width: 100%;
}

.group-library-dialog button {
	background: #1C576C;
	border: none;
	color: #fff;
	font-size: 16px;
	font-weight: normal;
	padding: 9px 15px;
}

.group-library-dialog button:hover {
	background: #022d3c;
	border: none;
	color: #fff;
}

.current-folder-breadcrumb {
	color: #1c576c;
	font-size: 16px;
	font-weight: 700;
	margin-top: 20px;
}

@media screen and (max-width:1200px) {
	.group-library-header {
		flex-direction: column;
		gap: 24px;
	}
}

@media screen and (max-width:600px) {
	.cac-group-library-inner {
		width: 97%;
	}

	.group-library-header {
		align-items: flex-start;
	}

	.group-library-header h2 {
		font-size: 28px;
	}

	.header-right {
		flex-direction: column;
		order: 1;
		width: 100%;
	}

	.header-right > .add-new-item-button {
		width: calc(100% - 32px);
	}

	.header-right .library-search {
		width: 100%;
	}

	.header-filters {
		flex-direction: column;
		order: 2;
		gap: 16px;
		width: 100%;
	}

	.library-search-input {
		line-height: 28px;
	}

	.group-library-header .library-search {
		margin-top: -2px;
	}

	.group-library-header .header-actions {
		margin-left: 0;
	}

	.group-library-filters {
		padding-right: 0;
	}

	.description-toggle {
		display: block;
		margin-top: 8px;
		position: relative;
		right: auto;
		text-align: right;
		top: auto;
		width: 100%;
	}
}
</style>
