<template>
	<span>
		<label
			v-bind:for="fieldId"
			class="screen-reader-only">
			{{title}}
		</label>

		<v-select
			v-bind:id="fieldId"
			v-model="selected"
			:options="opts">
		</v-select>
	</span>
</template>

<script>
	import vSelect from 'vue-select'
	import 'vue-select/dist/vue-select.css';

	export default {
		components: {
			vSelect
		},

		computed: {
			fieldId() {
				return 'filter-' + this.name
			},

			selected: {
				get: function() {
					const { currentItemType } = this.$store.state

					return this.opts.filter( itemType => currentItemType === itemType.code )
				},
				set: function( payload ) {
					this.$store.commit( 
						'setCurrentItemType', 
						{
							value: payload.code
						} 
					)
				}
			},
		},

		props: {
			name: String,
			opts: Array,
			title: String,
		}
	}
</script>

<style>
</style>
