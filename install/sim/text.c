/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: text.c,v 1.29 2020-08-14 16:56:53 dick Exp $
*/

#include	"text.h"

#include	"global.h"
#include	"stream.h"
#include	"Malloc.h"

struct text *Text;			/* to be filled in by Malloc() */
int Number_of_Texts;
int Number_of_New_Texts;

/*							TEXT INTERFACE */

void
Init_Text(int nfiles) {
	/* allocate the array of text descriptors */
	if (Text) {
		Free(Text);
		Text = 0;
	}
	Number_of_Texts = nfiles;
	Text = (struct text *)
		Malloc((size_t)(Number_of_Texts*sizeof (struct text)));
}

int
Open_Text(struct text *txt) {
	return Open_Stream(txt->tx_fname);
}

int
Next_Text_Token_Obtained(void) {
	return Next_Stream_Token_Obtained();
}

void
Close_Text(void) {
	/* Flush the flex buffers; it's easier than using YY_BUFFER_STATE. */
	while (Next_Text_Token_Obtained()) {
		/* skip */
	}

	Close_Stream();
}

void
Free_Text(void) {
	if (Text) {
		Free(Text); Text = 0;
	}
}

