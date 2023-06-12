/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: lang.c,v 2.17 2020-08-14 16:56:51 dick Exp $
*/

/*
	This is a dummy implementation of the  module 'lang'.
	Its actual implementation derives from the pertinent *lang.l file.
*/

#include	"lang.a"

#include	"global.h"
#include	"properties.h"
#include	"idf.h"
#include	"lex.h"
#include	"token.h"


FILE *yyin;

int
yylex(void) {
#ifdef	lint
	(void)May_Be_Start_Of_Run(0);
	(void)Best_Run_Size(0, 0);
	(void)Idf_In_List(0, 0, 0, 0);
	(void)Idf_Hashed(0);
	(void)Lower_Case(0);
#endif
	return 0;
}

void
yystart(void) {
#ifdef	lint
	Init_Language_Properties(0, 0, 0, 0);
#endif
}

Token lex_token;
size_t lex_nl_cnt;
size_t lex_tk_cnt;
size_t lex_non_ASCII_cnt;

void
Init_Language(void) {
}
