<template>
	<div id="cac-group-library-inner">
		<div class="group-library-header">
			<h2>Library</h2>
		</div>

		<div class="group-library-items">
			<ul>
				<li v-for="itemId in itemIds">
					<LibraryItem
						:itemId='itemId'
					/>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
	import LibraryItem from './LibraryItem.vue'

	/*
	import PanelTools from '../mixins/PanelTools.js'

	import ModalContent from './ModalContent.vue'
	import ModalHeader from './ModalHeader.vue'
	import ModalNav from './ModalNav.vue'
	*/

	export default {
		components: {
			LibraryItem
		},

		computed: {
			itemIds() {
				return this.$store.state.libraryItemIds
			}
		},

		methods: {
		}

		/*
		mixins: [
			PanelTools
		],
		*/
	}
</script>

<style>
body.groups.single-item.library #item-header {
	display: none;
}

#cac-group-library-inner {
	margin-top: 24px;
}
</style>
