( function () {
    'use strict';
    // Saved FINA map/timeline pages still use Bootstrap 4's dropdown attribute.
    mw.hook( 'wikipage.content' ).add( ( $content ) => {
        $content.find( '[data-toggle="dropdown"]' ).each( function () {
            let trigger = this;
            if ( trigger.tagName === 'DIV' ) {
                const button = document.createElement( 'button' );
                Array.from( trigger.attributes ).forEach( ( attribute ) => button.setAttribute( attribute.name, attribute.value ) );
                button.type = 'button';
                button.append( ...trigger.childNodes );
                trigger.replaceWith( button );
                trigger = button;
            }
            trigger.setAttribute( 'data-bs-toggle', 'dropdown' );
            trigger.setAttribute( 'aria-expanded', 'false' );
            trigger.setAttribute( 'aria-label', 'More pages' );
        } );
    } );
    function init() {
        const nav = document.querySelector( '.nav-container' );
        if ( !nav ) { return; }
        function closePanels( except ) {
            document.querySelectorAll( '.nav-dropdown, #p-search' ).forEach( ( panel ) => {
                if ( panel !== except && !panel.contains( except ) ) { panel.hidden = true; }
            } );
            document.querySelectorAll( '.kma-menu-toggle, .kma-control' ).forEach( ( button ) => {
                const panel = document.getElementById( button.getAttribute( 'aria-controls' ) ) || button.nextElementSibling;
                if ( panel ) { button.setAttribute( 'aria-expanded', String( !panel.hidden ) ); }
            } );
        }
        document.querySelectorAll( '.kma-menu-toggle' ).forEach( ( button ) => {
            button.addEventListener( 'click', () => {
                const panel = button.nextElementSibling;
                const open = panel.hidden;
                closePanels( panel );
                panel.hidden = !open;
                button.setAttribute( 'aria-expanded', String( open ) );
            } );
        } );
        document.querySelectorAll( '.kma-control' ).forEach( ( button ) => {
            const panel = document.getElementById( button.getAttribute( 'aria-controls' ) );
            if ( !panel ) { return; }
            button.setAttribute( 'aria-expanded', String( !panel.hidden ) );
            button.addEventListener( 'click', () => {
                const open = panel.hidden;
                closePanels( panel );
                panel.hidden = !open;
                button.setAttribute( 'aria-expanded', String( open ) );
                if ( panel.id === 'p-search' && open ) { panel.querySelector( 'input[type=search]' ).focus(); }
                if ( panel.id === 'p-contentnavigation' ) {
                    mw.cookie.set( 'kmaskin-show-nav-edit', open ? null : '1', { path: '/' } );
                }
            } );
        } );
        const toggle = document.getElementById( 'nav-toggle' );
        toggle.addEventListener( 'click', () => {
            const open = toggle.getAttribute( 'aria-expanded' ) !== 'true';
            toggle.setAttribute( 'aria-expanded', String( open ) );
            toggle.classList.toggle( 'active', open );
            nav.classList.toggle( 'kma-menu-open', open );
            closePanels();
        } );
        document.addEventListener( 'click', ( event ) => {
            if ( !event.target.closest( '.nav-container, #p-contentnavigation, .kma-toolbox' ) ) { closePanels(); }
        } );
        document.addEventListener( 'keydown', ( event ) => {
            if ( event.key === 'Escape' ) {
                const panel = event.target.closest( '.nav-dropdown, #p-search' );
                const trigger = panel && ( document.querySelector( '[aria-controls="' + panel.id + '"]' ) || panel.previousElementSibling );
                closePanels();
                if ( trigger ) { trigger.focus(); }
            }
        } );
    }
    if ( document.readyState === 'loading' ) { document.addEventListener( 'DOMContentLoaded', init ); } else { init(); }
}() );
