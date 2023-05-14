import ReactDOM from 'react-dom';

const MoveBlocks = (props) => {
    return ReactDOM.createPortal(
        props.children,
        document.getElementById( 'wprm-move-blocks' )
      );
}

export default MoveBlocks;