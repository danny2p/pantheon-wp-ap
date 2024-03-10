import ReactDOM from 'react-dom';

const AddPatterns = (props) => {
    return ReactDOM.createPortal(
        props.children,
        document.getElementById( 'wprm-add-patterns' )
      );
}

export default AddPatterns;