<template>
	<div id="cac-group-library-inner">
		<div class="add-edit-header edit-header">
			<div class="back-to-library">
				<span class="dashicons dashicons-arrow-left-alt"></span> <router-link to="/">Back to Library</router-link>
			</div>

			<h2>Edit Library Item</h2>
		</div>

		<div class="add-edit-content edit-content">
			<div class="add-new-content-form">
				<h3 class="edit-item-title">{{ getTitle() }}</h3>

				<BpGroupDocumentForm
					:itemId="getItemId()"
					v-if="'bp_group_document' === getItemType()"
				/>

				<ExternalLinkForm
					:itemId="getItemId()"
					v-if="'external_link' === getItemType()"
				/>
			</div>
		</div>
	</div>
</template>

<script>
	import BpGroupDocumentForm from '../components/Forms/BpGroupDocumentForm.vue'
	import ExternalLinkForm from '../components/Forms/ExternalLinkForm.vue'
	import ItemTypeDropdown from '../components/ItemTypeDropdown.vue'

	export default {
		components: {
			BpGroupDocumentForm,
			ExternalLinkForm,
			ItemTypeDropdown
		},

		computed: {
			initialLoadComplete() {
				return this.$store.state.initialLoadComplete
			}
		},

		created() {
			if ( this.initialLoadComplete ) {
				this.setUpItem()
			} else {
				this.$store.dispatch( 'refetchItems' ).then( () => this.setUpItem() )
			}
		},

		methods: {
			fillForm() {
				const item = this.getItem()

				// We use this value when deciding how to submit. See submitAddNew().
				let itemTypeSelectorValue

				switch ( this.getItemType() ) {
					case 'bp_group_document' :
						itemTypeSelectorValue = 'bpGroupDocument'

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'bpGroupDocument',
								field: 'itemId',
								value: item.id
							}
						)

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'bpGroupDocument',
								field: 'title',
								value: item.title
							}
						)

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'bpGroupDocument',
								field: 'description',
								value: item.description
							}
						)

						if ( item.hasOwnProperty( 'folders' ) && item.folders.length > 0 ) {
							this.$store.commit(
								'setFormFieldValue',
								{
									form: 'bpGroupDocument',
									field: 'folder',
									value: { code: item.folders[0], label: item.folders[0] }
								}
							)
						}
					break;

					case 'external_link' :
						itemTypeSelectorValue = 'externalLink'

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'externalLink',
								field: 'itemId',
								value: item.id
							}
						)

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'externalLink',
								field: 'title',
								value: item.title
							}
						)

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'externalLink',
								field: 'url',
								value: item.url
							}
						)

						if ( item.hasOwnProperty( 'folders' ) && item.folders.length > 0 ) {
							this.$store.commit(
								'setFormFieldValue',
								{
									form: 'externalLink',
									field: 'folder',
									value: { code: item.folders[0], label: item.folders[0] }
								}
							)
						}
					break;
				}

				this.$store.commit(
					'setItemTypeSelector',
					{
						value: itemTypeSelectorValue
					}
				)
			},

			getItem() {
				const { itemId } = this.$route.params

				if ( this.$store.state.libraryItems.hasOwnProperty( itemId ) ) {
					return this.$store.state.libraryItems[ itemId ]
				}

				return {}
			},

			getItemId() {
				const { id } = this.getItem()
				return id
			},

			getItemType() {
				const { item_type } = this.getItem()
				return item_type
			},

			getTitle() {
				const { title } = this.getItem()
				return title
			},

			setUpItem() {
				const item = this.getItem()
				const { can_edit, id } = item

				// No id means that the item doesn't exist.
				if ( ! id || ! can_edit ) {
					this.$router.push( { path: '/' } )
					return
				}

				this.fillForm()
			},
		}
	}
</script>

<style>
	.back-to-library {
		font-size: 12px;
		line-height: 20px;
		margin-bottom: 18px;
	}

	.back-to-library .dashicons {
		color: #1c576c;
		font-size: 14px;
		line-height: 20px;
	}
</style>
