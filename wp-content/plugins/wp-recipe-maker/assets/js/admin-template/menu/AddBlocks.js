import ReactDOM from 'react-dom';

const AddBlocks = (props) => {
    return ReactDOM.createPortal(
        props.children,
        document.getElementById( 'wprm-add-blocks' )
      );
}

export default AddBlocks;