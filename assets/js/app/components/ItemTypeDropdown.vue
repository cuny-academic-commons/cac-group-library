<template>
	<div class="item-type-selector">
		<label for="selected-item-type">What type of item do you want to add?</label>

		<div>
			<v-select
				id="selected-item-type"
				v-bind:clearable="false"
				v-model="selected"
				:options="opts">
			</v-select>
		</div>
	</div>
</template>

<script>
	import vSelect from 'vue-select'
	import 'vue-select/dist/vue-select.css';

	export default {
		components: {
			vSelect
		},

		computed: {
			opts() {
				return [
					{ code: '', label: 'Choose a type' },
					{ code: 'bpGroupDocument', label: 'Upload a file' },
					{ code: 'bpDoc', label: 'Create an editable doc' },
					{ code: 'externalLink', label: 'Add an external link' },
				]
			},

			selected: {
				get() {
					const { itemTypeSelector } = this.$store.state.forms

					for ( var i in this.opts ) {
						if ( this.opts[ i ].code === itemTypeSelector ) {
							return this.opts[ i ]
						}
					}
				},

				set( value ) {
					this.$store.commit(
						'setItemTypeSelector',
						{ value: value.code }
					)
				}
			}
		}

	}
</script>

<style>
.item-type-selector label {
	display: block;
	font-weight: 700;
	margin-bottom: 10px;
}

.item-type-selector .v-select {
	width: 300px;
}
</style>
