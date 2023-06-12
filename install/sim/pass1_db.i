/*	This file is part of the software similarity tester SIM.    -*-mode:C-*-
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: pass1_db.i,v 2.3 2020-08-14 16:56:52 dick Exp $
*/

/* activated by macro DB_TEXT */

static void
db_print_text(const struct text *txt) {
	/* prints a text (in compressed form) */
	size_t i;

	fprintf(Debug_File, "\n\n**** DB_PRINT_TEXT ****\n");

	fprintf(Debug_File, "File \"%s\", %s %ss, ",
		txt->tx_fname,
		size_t2string(txt->tx_limit - txt->tx_start),
		Token_Name
	);
	fprintf(Debug_File, "txt->tx_start = %s, txt->tx_limit = %s\n",
		size_t2string(txt->tx_start),
		size_t2string(txt->tx_limit)
	);

	int BoL = 1;
	for (i = txt->tx_start; i < txt->tx_limit; i++) {
		if (BoL) {
			fprintf(Debug_File, "[%s]:", size_t2string(i));
			BoL = 0;
		}
		fprintf(Debug_File, " ");
		fprint_Token(Debug_File, Token_Array[i]);
		if ((i - txt->tx_start + 1) % 10 == 0) {
			fprintf(Debug_File, "\n");
			BoL = 1;
		}
	}
	fprintf(Debug_File, "\n");
}
