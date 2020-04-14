<template>
	<span>
		<label
			v-bind:for="fieldId"
			class="screen-reader-only">
			{{title}}
		</label>

		<select
			v-bind:id="fieldId"
			v-model="selected">
			<option 
				v-for="opt in opts"
				v-bind:value="opt.name">
				{{opt.label}}
			</option>
		</select>
	</span>
</template>

<script>
	export default {
		computed: {
			fieldId() {
				return 'filter-' + this.name
			},

			selected: {
				get: function() {
					return this.$store.state.currentItemType
				},
				set: function( value ) {
					this.$store.commit( 
						'setCurrentItemType', 
						{
							value
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
