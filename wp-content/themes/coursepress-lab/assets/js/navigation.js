( function () {
    'use strict';

    var toggle = document.querySelector( '[data-coursepress-menu-toggle]' );
    var navigation = document.querySelector( '[data-coursepress-primary-navigation]' );
    var mobileQuery = window.matchMedia( '(max-width: 767px)' );

    if ( ! toggle || ! navigation ) {
        return;
    }

    document.documentElement.classList.add( 'coursepress-navigation-ready' );

    function setMenuState( isOpen ) {
        toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
        navigation.hidden = mobileQuery.matches && ! isOpen;
    }

    setMenuState( false );

    toggle.addEventListener( 'click', function () {
        setMenuState( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
    } );

    document.addEventListener( 'keydown', function ( event ) {
        if ( 'Escape' === event.key && mobileQuery.matches && 'true' === toggle.getAttribute( 'aria-expanded' ) ) {
            setMenuState( false );
            toggle.focus();
        }
    } );

    mobileQuery.addEventListener( 'change', function () {
        setMenuState( false );
    } );
}() );
