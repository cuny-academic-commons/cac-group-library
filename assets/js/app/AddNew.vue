<template>
	<div id="cac-group-library-inner">
		<div class="group-library-header">
			<h2>Library</h2>

			<button
				v-if='canCreateNew'
				v-on:click='onAddNewClick'
			>Add New Item</button>

			<div class="library-search">
				Search interface
			</div>
		</div>

		<transition name="fade">
			<div v-if="! isLoading" class="group-library-refreshable">
				<div class="group-library-nav">
					<div class="group-library-pagination">
						<Pagination />
					</div>

					<div class="group-library-filters">
						<FolderFilterDropdown />
						<ItemTypeFilterDropdown />
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
	import FolderFilterDropdown from './FilterDropdowns/FolderFilterDropdown.vue'
	import ItemTypeFilterDropdown from './FilterDropdowns/ItemTypeFilterDropdown.vue'
	import LibraryItem from './LibraryItem.vue'
	import Pagination from './Pagination.vue'
	import SortableColumnHeader from './SortableColumnHeader.vue'

	/*
	import PanelTools from '../mixins/PanelTools.js'

	import ModalContent from './ModalContent.vue'
	import ModalHeader from './ModalHeader.vue'
	import ModalNav from './ModalNav.vue'
	*/

	export default {
		components: {
			FolderFilterDropdown,
			ItemTypeFilterDropdown,
			LibraryItem,
			Pagination,
			SortableColumnHeader
		},

		computed: {
			canCreateNew() {
				return this.$store.state.canCreateNew
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

.group-library-header .library-search {
	margin-left: auto;
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
  transition: opacity .25s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>
