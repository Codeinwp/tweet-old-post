import { Button } from '@wordpress/components';

import { getUtmLink } from '../utils';

const Upsell = () => (
	<div
		style={{
			padding: '16px',
		}}
	>
		<h3
			style={{
				fontSize: '14px',
				margin: '0 0 8px',
				fontWeight: 600,
				display: 'flex',
				alignItems: 'center',
			}}
		>
			<span role="img" aria-label="unlock" style={{ marginRight: '6px' }}>
				🔓
			</span>
			{ropApiSettings.labels.post_editor.upsell.title}
		</h3>
		<p
			style={{
				marginBottom: '10px',
				color: '#555',
			}}
		>
			{ropApiSettings.labels.post_editor.upsell.subtitle}
		</p>
		<ul
			style={{
				margin: '0 0 12px 16px',
				padding: 0,
				listStyle: 'disc',
				color: '#444',
			}}
		>
			<li>{ropApiSettings.labels.post_editor.upsell.line_one}</li>
			<li>{ropApiSettings.labels.post_editor.upsell.line_two}</li>
			<li>{ropApiSettings.labels.post_editor.upsell.line_three}</li>
		</ul>
		<Button
			variant="primary"
			href={getUtmLink({
				link: ropApiSettings.upsell_link,
				source: 'post-editor',
				medium: 'sidebar',
				campaign: 'variations',
			})}
			target="_blank"
		>
			{ropApiSettings.labels.post_editor.upsell.cta}
		</Button>
	</div>
);

export default Upsell;
