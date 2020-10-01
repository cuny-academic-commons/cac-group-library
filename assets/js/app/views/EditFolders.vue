<template>
	<div id="cac-group-library-inner">
		<div class="add-edit-header edit-header">
			<div class="back-to-library">
				<span class="dashicons dashicons-arrow-left-alt"></span> <router-link to="/">Back to Library</router-link>
			</div>

			<h2>Library folders</h2>

		</div>

		<div class="edit-folders-content">
			<ul class="edit-folders">
				<li v-for="folder in foldersOfGroup">
					<EditFolder
						:folderName='folder'
					/>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
	import EditFolder from '../components/EditFolder.vue'

	export default {
		components: {
			EditFolder
		},

		computed: {
			foldersOfGroup() {
				return this.$store.state.foldersOfGroup
			},
		},

		created() {
			this.$store.commit( 'setUpFolderNamesForm' )
		},

		methods: {
		}
	}
</script>

<style>
	.edit-folders-content {
		margin-top: 20px;
	}

	.edit-folders li:first-child {
		border-top: 1px solid #ddd;
	}

	.edit-folders li {
		border-bottom: 1px solid #ddd;
		padding: 12px;
	}
</style>
