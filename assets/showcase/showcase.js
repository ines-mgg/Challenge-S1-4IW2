/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (showcase.css in this case)
import './styles/showcase.css';
import { startStimulusApp } from '@symfony/stimulus-bridge';

export const showcase = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!../controllers',
    true,
    /\.(j|t)sx?$/
));