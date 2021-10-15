import ReactDOM from 'react-dom';

const RemoveBlocks = (props) => {
    return ReactDOM.createPortal(
        props.children,
        document.getElementById( 'wprm-remove-blocks' )
      );
}

export default RemoveBlocks;