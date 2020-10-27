<template>
	<div id="cac-group-library-inner">
		<div :class="headerClass">
			<div class="header-with-actions">
				<h2>Library</h2>

				<div class="header-actions">
					<router-link
						class="add-new-item-button"
						v-if='canCreateNew'
						to='/new'
					>Add New Item</router-link>

					<SearchInput />
				</div>
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

					<div class="group-library-filters">
						<FolderFilterDropdown />
						<ItemTypeFilterDropdown />
					</div>

					<div
						class="current-folder-breadcrumb"
						v-if="'any' !== currentFolder"
					>{{currentFolderBreadcrumb}}</div>

					<div
						class="group-library-pagination"
					>
						<transition name="fade" mode="out-in">
							<span>
								<SearchResultsCount v-if="isSearchExpanded" key="search-results" />
								<Pagination v-else key="pagination" :showPagLinks="false" />
								<DescriptionToggle
									v-if="showDescriptionToggle"
								/>
							</span>
						</transition>
					</div>
				</div>

				<div
					:class="{ 'group-library-items': true, 'has-edit-column': showEditColumn }"
					id="group-library-items"
					v-if="showItemList"
				>
					<div class="group-library-column-headers group-library-row">
						<SortableColumnHeader
							label="Title"
							name="title"
							defaultSortOrder="asc"
						/>
						<SortableColumnHeader
							label="Added by"
							name="added-by"
							defaultSortOrder="asc"
						/>
						<SortableColumnHeader
							label="Date"
							name="date"
							defaultSortOrder="desc"
						/>
						<div
							class="group-library-column group-library-edit"
							v-if="showEditColumn"
						/>
					</div>

					<ul>
						<li v-for="itemId in paginatedItemIds">
							<LibraryItem
								:itemId='itemId'
							/>
						</li>
					</ul>

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
							<span>
								<SearchResultsCount v-if="isSearchExpanded" key="search-results" />
								<Pagination v-else key="pagination" :showPagLinks="true" />
							</span>
						</transition>
					</div>
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
				return 'In: ' + this.currentFolder
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

#cac-group-library-inner {
	margin-top: 24px;
}

.header-with-actions {
	align-items: center;
	display: flex;
}

.group-library-header {
	border-bottom: 2px solid #eeeeee;
	padding-bottom: 20px;
}

.group-library-header h2 {
	font-size: 28px;
}

.header-with-actions h2 {
	margin-right: 20px;
	white-space: nowrap;
}

.header-with-actions .header-actions {
	display: block;
	position: relative;
	margin-left: 20px;
	margin-right: 12px;
	height: 28px;
	width: 100%;
}

.group-library-header.search-is-expanded .header-actions {
	margin-right: 7px;
}

a.add-new-item-button {
	background: #1C576C 0% 0% no-repeat padding-box;
	border-radius: 4px;
	color: #fff;
	font-size: 11px;
	left: 0;
	line-height: 24px;
	padding: 0 12px;
	position: absolute;
	text-decoration: none;
	top: 2px;
}

a.add-new-item-button,
a.add-new-item-button:link,
a.add-new-item-button:visited {
	color: #fff;
}

a.add-new-item-button:hover {
	background-color: #4f8a95;
}

.group-library-header .library-search {
	position: absolute;
	right: 0;
	text-align: right;
	top: 0
}

.group-library-nav {
	padding: 15px 0;
}

.group-library-pagination {
	font-size: 11px;
	padding-top: 20px;
}

.group-library-column-headers {
	background: #f5f5f5;
	border-bottom: 1px solid #ddd;
	border-top: 1px solid #ddd;
	color: #666;
	font-weight: 700;
	line-height: 28px;
}

.group-library-row {
	display: flex;
	padding-left: 10px;
	padding-right: 30px;
	position: relative;
}

.group-library-item-title {
	flex-basis: 70%;
}

.group-library-item-added-by {
	flex-basis: 20%;
}

.group-library-item-date {
	flex-basis: 20%;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}

.search-is-expanded .add-new-item-button {
	opacity: 0;
}

.add-new-item-button {
	transition: opacity .25s;
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

@media screen and (max-width:600px) {
	.group-library-header {
		align-items: flex-start;
	}

	.group-library-header h2 {
		font-size: 24px;
	}

	.group-library-header .header-actions {
		margin-left: 0;
		padding-top: 40px;
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
