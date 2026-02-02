<template>
	<label class="include-forum-attachments-checkbox">
		<input
			type="checkbox"
			:checked="includeForumAttachments"
			@change="onChange"
		/>
		Include Forum Attachments
	</label>
</template>

<script>
	export default {
		computed: {
			includeForumAttachments() {
				const value = this.$store.state.route.query.hasOwnProperty( 'includeForumAttachments' ) 
					? decodeURIComponent( this.$store.state.route.query.includeForumAttachments ) 
					: 'false'
				return value === 'true'
			}
		},

		methods: {
			onChange( event ) {
				const newValue = event.target.checked ? 'true' : 'false'
				
				const newQuery = Object.assign( {}, this.$route.query, {
					includeForumAttachments: newValue,
					page: 1
				} )

				this.$router.push( {
					path: '/',
					query: newQuery
				} )
			}
		}
	}
</script>

<style>
.include-forum-attachments-checkbox {
	align-items: center;
	cursor: pointer;
	display: flex;
	gap: 8px;
	user-select: none;
}

.include-forum-attachments-checkbox input[type="checkbox"] {
	cursor: pointer;
	margin: 0;
}
</style>
