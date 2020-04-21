<template>
	<div id="cac-group-library-inner">
		<div :class="headerClass">
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

		<transition name="fade">
			<div v-if="! isLoading" class="group-library-refreshable">
				<div class="group-library-nav">
					<div class="group-library-pagination">
						<transition name="fade" mode="out-in">
							<SearchResultsCount v-if="isSearchExpanded" key="search-results" />
							<Pagination v-else key="pagination" />
						</transition>
					</div>

					<div class="group-library-filters">
						<FolderFilterDropdown />
						<ItemTypeFilterDropdown />
						<DescriptionToggle />
					</div>
				</div>

				<div class="group-library-items" id="group-library-items">
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
					</div>

					<ul>
						<li v-for="itemId in paginatedItemIds">
							<LibraryItem
								:itemId='itemId'
							/>
						</li>
					</ul>
				</div>
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

	/*
	import PanelTools from '../mixins/PanelTools.js'

	import ModalContent from './ModalContent.vue'
	import ModalHeader from './ModalHeader.vue'
	import ModalNav from './ModalNav.vue'
	*/

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

			headerClass() {
				let itemClasses = [ 'group-library-header' ]

				if ( this.isSearchExpanded ) {
					itemClasses.push( 'search-is-expanded' )
				}

				return itemClasses.join( ' ' )
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
				}
			},

			paginatedItemIds() {
				return this.$store.state.paginatedItemIds
			},

			isLoading() {
				return this.$store.state.isLoading
			},
		},

		created() {
			this.$store.commit( 'refreshFilteredItemIds' )
		},

		methods: {

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

#cac-group-library-inner {
	margin-top: 24px;
}

.group-library-header {
	align-items: center;
	border-bottom: 2px solid #eeeeee;
	display: flex;
	padding-bottom: 20px;
}

.group-library-header h2 {
	font-size: 28px;
	margin-right: 20px;
}

.group-library-header .header-actions {
	display: block;
	position: relative;
	margin-left: 30px;
	margin-right: 12px;
	height: 28px;
	width: 100%;
}

.group-library-header.search-is-expanded .header-actions {
	margin-right: 7px;
}

.add-new-item-button {
	position: absolute;
}

.group-library-header .library-search {
	position: absolute;
	right: 0;
	text-align: right;
	top: 0
}

.group-library-nav {
	padding: 20px 0;
}

.group-library-pagination {
	font-size: 11px;
	padding-bottom: 20px;
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
	padding-right: 10px;
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
	top: 0;
}
</style>
