import ReactDOM from 'react-dom';

const BlockProperties = (props) => {
    return ReactDOM.createPortal(
        props.children,
        document.getElementById( 'wprm-block-properties' )
      );
}

export default BlockProperties;