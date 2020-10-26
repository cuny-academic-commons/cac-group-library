<template>
	<div id="cac-group-library-inner">
		<div class="add-edit-header edit-header">
			<div class="back-to-library">
				<span class="dashicons dashicons-arrow-left-alt"></span> <router-link to="/">Back to Library</router-link>
			</div>

			<div class="header-with-actions">
				<h2>Manage folders</h2>

				<div class="header-actions">
					<a
						v-on:click="onAddNewClick"
						class="add-new-item-button"
						href="#/editFolders"
					>Add New Folder</a>
				</div>
			</div>

		</div>

		<div class="edit-folders-content">
			<AddFolder
				v-if="isAddNewMode"
			/>

			<ul :class="listClass">
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
	import AddFolder from '../components/AddFolder.vue'
	import EditFolder from '../components/EditFolder.vue'

	export default {
		components: {
			AddFolder,
			EditFolder
		},

		computed: {
			foldersOfGroup() {
				return this.$store.state.foldersOfGroup
			},

			isAddNewMode: {
				get() {
					return '_new' === this.$store.state.currentlyEditedFolder	
				},

				set( value ) {
					this.$store.commit( 'setCurrentlyEditedFolder', { value: '_new' } );
				}
			},

			listClass() {
				let theClass = 'edit-folders'

				if ( this.$store.state.deleteInProgress ) {
					theClass += ' delete-in-progress'
				}

				return theClass
			}
		},

		created() {
			this.$store.commit( 'setUpFolderNamesForm' )
		},

		methods: {
			onAddNewClick() {
				this.isAddNewMode = true
			}
		},

		mounted() {
			this.$store.commit( 'setUpFolderNamesForm' )
		},
	}
</script>

<style>
	.edit-folders-content {
		margin-top: 20px;
	}

	.edit-folders li:first-child {
		border-top: 1px solid #ddd;
	}

	.edit-folders.delete-in-progress {
		opacity: .5;
	}

	.edit-folders.delete-in-progress button,
	.edit-folders.delete-in-progress a {
		cursor: default;
		pointer-events: none;
	}

	.edit-folders li {
		border-bottom: 1px solid #ddd;
		padding: 12px;
	}

	@media screen and (max-width:600px) {
		.edit-folders li {
			padding: 12px 6px;
		}

		.edit-folder-actions {
			white-space: nowrap;
		}
	}
</style>
